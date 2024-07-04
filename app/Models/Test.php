<?php
namespace App\Http\Controllers;

use App\Services\TestService;
use Illuminate\Http\Request;

class TestController extends Controller
{
    protected $testService;

    public function __construct(TestService $testService)
    {
        $this->testService = $testService;
    }

    public function index()
    {
        $tests = $this->testService->getAllTests();
        return response()->json($tests);
    }

    public function store(Request $request)
    {
        $test = $this->testService->createTest($request->all());
        return response()->json($test, 201);
    }

    public function show($id)
    {
        $test = $this->testService->getTestById($id);
        return response()->json($test);
    }

    public function update(Request $request, $id)
    {
        $test = $this->testService->updateTest($id, $request->all());
        return response()->json($test);
    }

    public function destroy($id)
    {
        $this->testService->deleteTest($id);
        return response()->json(null, 204);
    }
}
