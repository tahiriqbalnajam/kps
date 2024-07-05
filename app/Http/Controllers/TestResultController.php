<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Laravue\JsonResponse;
use App\Services\TestService;

class TestResultController extends Controller
{
    protected $testResultService;

    public function __construct(TestService $testResultService)
    {
        $this->testResultService = $testResultService;
    }

    public function index(Request $request)
    {
        $testResults = $this->testResultService->getAllTestResults($request->all());
        return response()->json(new JsonResponse(['results' => $testResults]));
    }

    public function store(Request $request)
    {
        $testResult = $this->testResultService->createTestResult($request->all());
        return response()->json($testResult, 201);
    }

    public function show($id)
    {
        $testResult = $this->testResultService->getTestResultById($id);
        return response()->json($testResult);
    }

    public function update(Request $request, $id)
    {
        $testResult = $this->testResultService->updateTestResult($id, $request->all());
        return response()->json($testResult);
    }

    public function destroy($id)
    {
        $this->testResultService->deleteTestResult($id);
        return response()->json(null, 204);
    }
}