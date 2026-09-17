<?php

namespace App\Traits;

use App\Laravue\JsonResponse;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * Attach a user account to a record that has a user_id column (parents,
 * teachers, ...).
 *
 * One person can be both a teacher and a parent, so a single user account may
 * hold several roles. That is why an existing account is never merged
 * silently: the caller has to confirm it (link_existing), because the email
 * may also belong to a completely different person.
 */
trait LinksUserAccounts
{
    /**
     * @param  \Illuminate\Database\Eloquent\Model  $record  model with a user_id column
     * @param  string  $roleName  role to give the account ('parent', 'teacher', ...)
     */
    protected function attachUserAccount($record, Request $request, string $roleName)
    {
        $linkExisting = $request->boolean('link_existing');

        $request->validate([
            'email' => 'required|email',
            // Only needed when a new account is built — an existing one keeps
            // its own password.
            'password' => $linkExisting ? 'nullable|string|min:6' : 'required|string|min:6',
        ]);

        $existing = User::where('email', $request->email)->first();

        if ($existing && ! $linkExisting) {
            // Let the caller ask the admin before touching someone's account.
            return response()->json([
                'success' => false,
                'code' => 'email_exists',
                'message' => 'A user account with this email already exists.',
                'existing_user' => [
                    'id' => $existing->id,
                    'name' => $existing->name,
                    'roles' => $existing->getRoleNames(),
                ],
            ], 409);
        }

        if ($existing) {
            // assignRole (not syncRoles) so the roles the account already holds
            // survive — a teacher linked to a parent record stays a teacher.
            $existing->assignRole(Role::findByName($roleName));

            $record->user_id = $existing->id;
            $record->saveQuietly(); // skip boot events to avoid re-triggering user creation

            return response()->json(new JsonResponse(['user' => $existing, 'linked' => true]));
        }

        $user = User::create([
            'name' => $record->name,
            'email' => $request->email,
            'phone' => $record->phone,
            'password' => Hash::make($request->password),
        ]);

        $user->assignRole(Role::findByName($roleName));

        $record->user_id = $user->id;
        $record->saveQuietly();

        return response()->json(new JsonResponse(['user' => $user]));
    }

    /**
     * Remove a user account along with the record that owns it.
     *
     * The account is only deleted when this was its only role. A user can be
     * several things at once (a teacher who is also a parent), so when other
     * roles remain the account is kept and just this role is detached —
     * otherwise deleting the parent record would destroy the teacher's login.
     */
    protected function deleteOrDetachUserAccount($record, string $roleName)
    {
        $user = $record->user;

        if (! $user) {
            return;
        }

        $otherRoles = $user->getRoleNames()->reject(fn ($name) => $name === $roleName);

        if ($otherRoles->isNotEmpty()) {
            $user->removeRole($roleName);

            return;
        }

        $user->delete();
    }
}
