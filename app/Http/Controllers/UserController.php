<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\User\StoreUser;
use App\Http\Resources\User\UserResource;

class UserController extends Controller
{
    function index()
    {
        try {
            $user = User::with('role')->get();

            return response()->json([
                'success' => true,
                'data' => UserResource::collection($user)
            ]);
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    function store(StoreUser $request)
    {
        try {
            $validated = $request->validated();

            $user = User::create([
                'name'     => $validated['name'],
                'email'    => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role_id'  => 2,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'User berhasil ditambahkan',
                'data' => UserResource::make($user)
            ]);
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users,email,' . $id,
                'password' => 'nullable|string|min:6|confirmed',
            ]);

            $user = User::find($id);
            $user->update($validated);
            return response()->json([
                'success' => true,
                'message' => 'User berhasil diperbarui',
                'data' => UserResource::make($user)
            ]);
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    function destroy($id)
    {
        try {
            $user = User::find($id);
            $user->delete();
            return response()->json([
                'success' => true,
                'message' => 'User berhasil dihapus',
                'data' => $user
            ]);
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}
