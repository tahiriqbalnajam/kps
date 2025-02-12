<?php

namespace App\Http\Controllers;

use App\Models\Settings;
use Illuminate\Http\Request;
use App\Laravue\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Response;

class SettingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // $school_id = session('school_id');
        $school_id = 1;
        $settings = Settings::find($school_id);
        return response()->json(new JsonResponse(['settings' => $settings]));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $data = $request->all();
            
            // Handle logo upload if present
            if ($request->hasFile('logo')) {
                $file = $request->file('logo');
                $fileName = 'school-logo-' . time() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('uploads/school'), $fileName);
                $data['logo'] = 'uploads/school/' . $fileName;
                
                // Delete old logo if exists
                $oldLogo = Settings::where('id', '1')->first();
                if ($oldLogo && file_exists(public_path($oldLogo->logo))) {
                    unlink(public_path($oldLogo->logo));
                }
            }
            // Save each setting
            Settings::where('id', '1')->update($data);
            // foreach ($data as $key => $value) {
            //     Settings::updateOrCreate(
            //         ['key' => $key],
            //         ['value' => $value]
            //     );
            // }

            return response()->json([
                'success' => true,
                'message' => 'Settings saved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error saving settings: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
