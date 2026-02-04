<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

/**
 * Class AuthController
 *
 * @package App\Http\Controllers\Api
 */
class AuthController extends BaseController
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);
        $user = User::query()->with(['student', 'roles'])->where('email', $request->input('email'))->first();
        if (empty($user) || !Hash::check($request->input('password'), $user->password)) {
            return responseFailed('These credentials do not match our records.', Response::HTTP_UNAUTHORIZED);
        }
        session(['user_id' => $user->id]);
        $user->token = $user->createToken('laravel-vue-admin')->plainTextToken;

        return responseSuccess($user);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        Auth::guard('web')->logout();
        return responseSuccess();
    }

    public function user(Request $request): UserResource
    {
        return new UserResource($request->user());
    }


    /**
     * Update the device token for push notifications.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updateDeviceToken(Request $request)
    {
        $request->validate([
            'player_id' => 'required|string',
            'user_id' => 'required|exists:users,id',
        ]);

        $user = User::find($request->user_id);
        
        $user->player_id = $request->player_id;
        $user->save();

        return responseSuccess(['message' => 'Player ID updated successfully', 'player_id' => $user->player_id]);
    }

    /**
     * Send a test push notification to the user.
     * 
     * @param Request $request
     */
    public function sendTestNotification(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'title' => 'required|string',
            'body' => 'required|string',
        ]);

        $user = User::find($request->user_id);

        if (!$user->player_id) {
             return responseFailed('User does not have a player_id.', 400);
        }

        try {
            $user->notify(new \App\Notifications\TestOneSignalNotification($request->title, $request->body));
            return responseSuccess(['message' => 'Notification sent successfully']);
        } catch (\Exception $e) {
            return responseFailed('Failed to send notification: ' . $e->getMessage(), 500);
        }
    }
}
