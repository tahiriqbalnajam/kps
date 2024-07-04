<?php
namespace App\Http\Controllers;

use App\Services\TestResultService;
use Illuminate\Http\Request;

class TestResultController extends Controller
{
    protected $testResultService;

    public function __construct(TestResultService $testResultService)
    {
        $this->testResultService = $testResultService;
    }

    public function index()
    {
        $testResults = $this->testResultService->getAllTestResults();
        return response()->json($testResults);
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