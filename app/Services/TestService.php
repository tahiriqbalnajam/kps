<?php
namespace App\Services;

use App\Models\Test;
use App\Models\TestResult;
use Illuminate\Http\Request;

class TestService
{
    public function getAllTests()
    {
        return Test::all();
    }

    public function createTest(array $data)
    {
        $validatedData = $this->validateTestData($data);
        return Test::create($validatedData);
    }

    public function getTestById($id)
    {
        return Test::with('testResults')->findOrFail($id);
    }

    public function updateTest($id, array $data)
    {
        $validatedData = $this->validateTestData($data);
        $test = Test::findOrFail($id);
        $test->update($validatedData);
        return $test;
    }

    public function deleteTest($id)
    {
        $test = Test::findOrFail($id);
        $test->delete();
        return response()->json(null, 204);
    }

    protected function validateTestData(array $data)
    {
        return validator($data, [
            'class_id' => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'title' => 'required|string|max:255',
            'date' => 'required|date',
        ])->validate();
    }

    public function getAllTestResults()
    {
        return TestResult::all();
    }

    public function createTestResult(array $data)
    {
        $validatedData = $this->validateTestResultData($data);
        return TestResult::create($validatedData);
    }

    public function getTestResultById($id)
    {
        return TestResult::findOrFail($id);
    }

    public function updateTestResult($id, array $data)
    {
        $validatedData = $this->validateTestResultData($data);
        $testResult = TestResult::findOrFail($id);
        $testResult->update($validatedData);
        return $testResult;
    }

    public function deleteTestResult($id)
    {
        $testResult = TestResult::findOrFail($id);
        $testResult->delete();
        return response()->json(null, 204);
    }

    protected function validateTestResultData(array $data)
    {
        return validator($data, [
            'test_id' => 'required|exists:tests,id',
            'student_id' => 'required|exists:students,id',
            'score' => 'required|numeric',
        ])->validate();
    }
}