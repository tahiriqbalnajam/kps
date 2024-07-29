<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Timetable;

class TimetableController extends Controller
{
    public function index() {
        $timetable = Timetable::find(1);
        return response()->json(['timetable' => $timetable]);
    }
    public function update(Request $request) {
        $timetable = Timetable::find(1);
        
        if ($timetable) {
            $timetable->update($request->all());
            return response()->json(['timetable' => $timetable]);
        } else {
            return response()->json(['error' => 'No timetable record found'], 404);
        }
    }
}
