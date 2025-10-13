<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    // In your controller
    public function saveOneSignalId(Request $request)
    {
        $request->validate([
            'onesignal_id' => 'required|string'
        ]);

        // Save to user profile or separate table
        if (auth()->check()) {
            auth()->user()->update([
                'onesignal_id' => $request->onesignal_id
            ]);
        }

        return response()->json(['success' => true]);
    }

}
