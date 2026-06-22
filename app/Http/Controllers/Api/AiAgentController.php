<?php

namespace App\Http\Controllers\Api;

use App\Ai\Agents\DataQueryAgent;
use App\Http\Controllers\Controller;
use App\Laravue\JsonResponse;
use App\Models\AiReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AiAgentController extends Controller
{
    /**
     * Run a natural-language query against the school data agent.
     */
    public function query(Request $request)
    {
        $data = $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        $user = Auth::user();
        $message = $data['message'];

        try {
            $response = (new DataQueryAgent($user))->prompt($message);
        } catch (\Throwable $e) {
            Log::error('AI agent prompt failed', [
                'message' => mb_substr($message, 0, 120),
                'error' => $e->getMessage(),
            ]);

            return response()->json(
                new JsonResponse(['error' => $e->getMessage()], 'error'),
                500
            );
        }

        $text = (string) $response;
        $decoded = $this->extractJson($text);

        $toolsUsed = $response->toolCalls
            ->map(fn ($call) => $call->name ?? null)
            ->filter()
            ->unique()
            ->values()
            ->all();

        $toolResults = $response->toolResults
            ->map(fn ($r) => ['name' => $r->name ?? null, 'result' => $r->result ?? null])
            ->values()
            ->all();

        if (is_array($decoded)) {
            $payload = $decoded;
        } elseif (trim($text) !== '') {
            // Model returned prose (no JSON) — show it as the summary.
            $payload = ['summary' => $text, 'highlights' => [], 'table' => ['columns' => [], 'rows' => []], 'sources' => []];
        } else {
            // Model produced no text (e.g. hit the step/timeout cap mid-tool-calling).
            // Surface the tool results so the user isn't left with an empty response.
            $payload = $this->payloadFromToolResults($toolResults);
        }

        if (! empty($toolsUsed) && empty($payload['sources'] ?? null)) {
            $payload['sources'] = $toolsUsed;
        }

        Log::info('AI agent response', [
            'message' => mb_substr($message, 0, 120),
            'steps' => $response->steps->count(),
            'text_len' => strlen($text),
            'text_head' => mb_substr($text, 0, 200),
            'tools' => $toolsUsed,
        ]);

        // Best-effort audit trail.
        try {
            AiReport::create([
                'title' => mb_substr($message, 0, 120),
                'prompt' => $message,
                'data_sources' => $toolsUsed,
                'analysis_result' => $payload['summary'] ?? $text,
                'metadata' => [
                    'user_id' => $user?->id,
                    'payload' => $payload,
                ],
                'status' => 'completed',
            ]);
        } catch (\Throwable $e) {
            // Audit failure must not break the response.
        }

        return response()->json(new JsonResponse($payload));
    }

    /**
     * Build a fallback payload from tool results when the model produced no text.
     */
    protected function payloadFromToolResults(array $toolResults): array
    {
        foreach ($toolResults as $tr) {
            $raw = is_string($tr['result'] ?? null) ? $tr['result'] : json_encode($tr['result'] ?? null);
            $data = json_decode($raw, true);

            if (! is_array($data)) {
                continue;
            }

            // A list of records -> render as a table.
            foreach (['students', 'records', 'vouchers', 'recent_vouchers', 'rows'] as $key) {
                if (isset($data[$key]) && is_array($data[$key]) && ! empty($data[$key])) {
                    $rows = $data[$key];
                    $columns = is_array($rows[0] ?? null) ? array_keys($rows[0]) : [];

                    return [
                        'summary' => 'Retrieved '.count($rows).' record(s) via '.($tr['name'] ?? 'a tool').'. (The model did not finish formatting the answer — showing the raw tool data.)',
                        'highlights' => [],
                        'table' => ['columns' => $columns, 'rows' => $rows],
                        'chart' => null,
                        'sources' => [],
                    ];
                }
            }

            // A scalar/summary result.
            if (isset($data['summary']) || isset($data['count']) || isset($data['error'])) {
                return [
                    'summary' => $data['summary'] ?? ($data['error'] ?? ('Count: '.($data['count'] ?? '?'))),
                    'highlights' => [],
                    'table' => ['columns' => [], 'rows' => []],
                    'chart' => null,
                    'sources' => [],
                ];
            }
        }

        return [
            'summary' => 'The assistant could not produce a response for that query. Please try rephrasing it.',
            'highlights' => [],
            'table' => ['columns' => [], 'rows' => []],
            'sources' => [],
        ];
    }

    /**
     * Extract a JSON object from the model's text response.
     * Handles raw JSON, ```json fenced blocks, and prose-wrapped JSON.
     */
    protected function extractJson(string $text): ?array
    {
        $text = trim($text);

        // Direct decode first.
        $decoded = json_decode($text, true);
        if (is_array($decoded)) {
            return $decoded;
        }

        // Strip a markdown code fence (```json ... ``` or ``` ... ```).
        if (preg_match('/```(?:json)?\s*(.*?)```/is', $text, $m)) {
            $decoded = json_decode(trim($m[1]), true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        // Grab the first balanced {...} block.
        if (preg_match('/\{.*\}/s', $text, $m)) {
            $decoded = json_decode($m[0], true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return null;
    }
}