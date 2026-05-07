<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;

class SuperAdminController extends Controller
{
    private function checkAuth()
    {
        return Session::get('super_authenticated') === true;
    }

    public function showForm()
    {
        if (!$this->checkAuth()) {
            return view('super.login');
        }

        return view('super.index');
    }

    public function login(Request $request)
    {
        $request->validate(['password' => 'required|string']);

        if ($request->password === config('app.super_password')) {
            Session::put('super_authenticated', true);
            return redirect()->route('super');
        }

        return back()->withErrors(['password' => 'Invalid password.']);
    }

    public function logout()
    {
        Session::forget('super_authenticated');
        return redirect()->route('super');
    }

    public function runQuery(Request $request)
    {
        if (!$this->checkAuth()) {
            return redirect()->route('super');
        }

        $request->validate(['query' => 'required|string']);

        $sql = $request->input('query');
        $statements = $this->parseStatements($sql);
        $databases = DB::table('databases')->get();
        $results = [];

        $dryRun = $request->has('dry_run');

        foreach ($databases as $db) {
            $entry = [
                'user'      => $db->user,
                'db'        => $db->db,
                'status'    => 'success',
                'message'   => '',
            ];

            if ($dryRun) {
                $entry['status'] = 'dry_run';
                $entry['message'] = count($statements) . ' statement(s) would execute.';
            } else {
                try {
                    Config::set('database.connections._super_run', [
                        'driver'    => 'mysql',
                        'host'      => 'mysql.idlschool.pk',
                        'port'      => env('DB_PORT', '3306'),
                        'database'  => $db->db,
                        'username'  => $db->username,
                        'password'  => $db->password,
                        'charset'   => 'utf8mb4',
                        'collation' => 'utf8mb4_unicode_ci',
                        'prefix'    => '',
                    ]);

                    $conn = DB::connection('_super_run');
                    foreach ($statements as $stmt) {
                        $conn->statement($stmt);
                    }
                    DB::purge('_super_run');
                    $entry['message'] = count($statements) . ' statement(s) executed successfully.';
                } catch (\Throwable $e) {
                    DB::purge('_super_run');
                    $entry['status'] = 'error';
                    $entry['message'] = $e->getMessage();
                }
            }

            $results[] = $entry;
        }

        return view('super.index', [
            'results' => $results,
            'query'   => $sql,
            'dryRun'  => $dryRun,
        ]);
    }

    private function parseStatements(string $sql): array
    {
        $statements = [];
        $current = '';
        $lines = explode("\n", $sql);

        foreach ($lines as $line) {
            $trimmed = trim($line);

            // Skip empty lines and pure comment lines
            if ($trimmed === '' || str_starts_with($trimmed, '--') || str_starts_with($trimmed, '#')) {
                continue;
            }

            $current .= $line . "\n";

            if (str_ends_with(rtrim($line), ';')) {
                $stmt = trim($current);
                if ($stmt !== '' && $stmt !== ';') {
                    $statements[] = $stmt;
                }
                $current = '';
            }
        }

        // Catch any trailing statement without semicolon
        $remainder = trim($current);
        if ($remainder !== '' && $remainder !== ';') {
            $statements[] = $remainder;
        }

        return $statements;
    }
}
