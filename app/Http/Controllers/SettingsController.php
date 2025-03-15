<?php

namespace App\Http\Controllers;

use App\Models\Settings;
use Illuminate\Http\Request;
use App\Laravue\JsonResponse;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = Settings::pluck('setting_value', 'setting_key');
        return response()->json(new JsonResponse(['settings' => $settings]));
    }

    public function store(Request $request)
    {
        if ($request->hasFile('school_logo')) {
            $file = $request->file('school_logo');

            // Validate the file
            $this->validate($request, [
                'school_logo' => 'image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);

            // Create directory if it doesn't exist
            $uploadPath = public_path('uploads/school');
            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0777, true);
            }

            // Generate unique filename
            $fileName = 'school_logo_' . time() . '.' . $file->getClientOriginalExtension();

            // Move the file to permanent location
            if ($file->move($uploadPath, $fileName)) {
                // Save the relative path (not full server path) to the database
                Settings::updateOrCreate(
                    ['setting_key' => 'school_logo'],
                    ['setting_value' => 'uploads/school/' . $fileName]
                );
            }
        }

        // Handle all other form inputs
        foreach ($request->except(['school_logo', '_token']) as $key => $value) {
            Settings::updateOrCreate(
                ['setting_key' => $key],
                ['setting_value' => $value]
            );
        }

        return response()->json(new JsonResponse(['message' => 'Settings updated successfully']));
    }

    public function show($key)
    {
        $setting = Settings::where('setting_key', $key)->first();
        return response()->json(new JsonResponse(['setting' => $setting]));
    }

    public function update(Request $request, $key)
    {
        $setting = Settings::where('setting_key', $key)->first();
        if ($setting) {
            $setting->setting_value = $request->value;
            $setting->save();
        } else {
            Settings::create([
                'setting_key' => $key,
                'setting_value' => $request->value
            ]);
        }

        return response()->json(new JsonResponse(['message' => 'Setting updated successfully']));
    }

    public function defaultMessageChannel()
    {
        $message_channel = Settings::where('setting_key', 'message_channel')->select(['setting_key', 'setting_value'])->first();
        return response()->json(new JsonResponse(['message_channel' => $message_channel]));
    }
}
