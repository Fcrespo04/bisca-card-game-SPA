<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return UserResource::collection(User::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request)
    {
        $user = User::create($request->validated());
        return new UserResource($user);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        return new UserResource($user);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $user->update($request->validated());
        return new UserResource($user);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, User $user)
    {
        $authenticatedUser = $request->user();

        // Admins cannot be deleted
        if ($authenticatedUser->id === $user->id && $authenticatedUser->type === 'A') {
            return response()->json(['message' => 'Administrators cannot delete their own account.'], 403);
        }

        // Password Confirmation (If deleting own account)
        if ($authenticatedUser->id === $user->id) {
            $request->validate([
                'password' => 'required|string'
            ]);

            if (!\Illuminate\Support\Facades\Hash::check($request->password, $user->password)) {
                return response()->json(['message' => 'Invalid password.'], 422);
            }
        }

        // Forfeit Coins 
        $user->coins_balance = 0;
        $user->save();

        // Soft and Force Delete 
        // check if the user has any game or transaction history
        $hasHistory = $user->transactions()->exists() 
                   || $user->gamesAsPlayer1()->exists() 
                   || $user->gamesAsPlayer2()->exists();

        if ($hasHistory) {
            // Soft Delete: Sets 'deleted_at' timestamp
            $user->delete();
            return response()->json(['message' => 'Account deactivated (soft delete).']);
        } else {
            // Force Delete: Removes row from DB completely
            if ($user->photo_avatar_filename) {
                Storage::disk('public')->delete('photos/' . $user->photo_avatar_filename);
            }
            
            $user->forceDelete();
            return response()->json(['message' => 'Account permanently deleted.']);
        }
    }

public function patchPhotoURL(Request $request, User $user)
{
    $data = $request->validate(['photo_avatar_filename' => 'required|string']);
    if ($user->photo_avatar_filename) {
        if (Storage::disk('public')->exists('photos/' . $user->photo_avatar_filename)) {
            Storage::disk('public')->delete('photos/' . $user->photo_avatar_filename);
        }
    }
    $user->photo_avatar_filename = basename($data['photo_avatar_filename']);
    $user->save();
    return new UserResource($user);
}
}
