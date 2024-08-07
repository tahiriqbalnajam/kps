<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Laravue\JsonResponse;
use App\Services\TestService;

class TestController extends Controller
{
    protected $testService;

    public function __construct(TestService $testService)
    {
        $this->testService = $testService;
    }

    public function index(Request $request)
    {
        $data = $request->all();
        $tests = $this->testService->getAllTests($data);
        return response()->json(new JsonResponse(['tests' => $tests]));
    }

    public function store(Request $request)
    {
        $test = $this->testService->createTestWithResults($request->all());
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
