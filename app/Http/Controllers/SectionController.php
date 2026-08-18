<?php

namespace App\Http\Controllers;

use App\Laravue\JsonResponse;
use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SectionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        $keyword = $request->keyword;
        $class_id = $request->class_id;

        $sections = Section::select('sections.*')
            ->withCount([
                'students',
                'students as males_count' => function ($query) {
                    $query->where('gender', 'male');
                },
                'students as females_count' => function ($query) {
                    $query->where('gender', 'female');
                },
            ])
            ->when($keyword, function ($query) use ($keyword) {
                return $query->where('sections.name', 'like', '%'.$keyword.'%');
            })
            ->when($class_id, function ($query) use ($class_id) {
                return $query->where('sections.class_id', $class_id);
            })
            // Default to enabled sections; pass status=disable or status=all to widen
            ->when($request->get('status') !== 'all', function ($query) use ($request) {
                return $query->where('sections.status', $request->get('status') ?: 'enable');
            })
            ->paginate($request->input('limit', 30));

        return response()->json(new JsonResponse(['sections' => $sections]));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request)
    {
        $section = new Section;
        $section->name = $request->name;
        $section->class_id = $request->class_id;
        $section->status = $request->input('status', 'enable');
        $section->save();

        return response()->json(new JsonResponse(['section' => $section]));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        $section = Section::findOrFail($id);

        return response()->json(new JsonResponse(['section' => $section]));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        $section = Section::findOrFail($id);
        $section->name = $request->name;
        if ($request->has('status')) {
            $section->status = $request->status;
        }
        $section->save();

        return response()->json(new JsonResponse(['section' => $section]));
    }

    /**
     * Toggle a section between enabled and disabled.
     *
     * @return Response
     */
    public function toggleStatus(Section $section)
    {
        $section->status = $section->status === 'disable' ? 'enable' : 'disable';
        $section->save();

        return response()->json(new JsonResponse(['section' => $section]));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        $section = Section::findOrFail($id);
        $section->delete();

        return response()->json(new JsonResponse('Deleted successfully'));
    }
}
