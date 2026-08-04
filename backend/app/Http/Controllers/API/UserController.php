<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::all()->map(function($user){
            return [
                'id' => $user->id,
                'name' => $user->name,
                'birthDate' => optional($user->birth_date)?->toDateString(),
                'role' => $user->role,
                'email' => $user->email,
                'createdBy' => $user->created_by,
                'createdDate' => optional($user->created_date)?->toDateTimeString(),
                'updatedBy' => $user->updated_by,
                'updatedDate' => optional($user->updated_date)?->toDateTimeString(),
            ];
        });

        return response()->json($users);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'birthDate' => 'nullable|date',
            'role' => 'required|string|max:100',
            'email' => 'nullable|email|max:255',
            'password' => 'nullable|string|min:6',
            'createdBy' => 'nullable|integer',
            'createdDate' => 'nullable|date',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'birth_date' => $data['birthDate'] ?? null,
            'role' => $data['role'],
            'email' => $data['email'] ?? null,
            'password' => $data['password'] ?? null,
            'created_by' => $data['createdBy'] ?? null,
            'created_date' => $data['createdDate'] ?? now(),
        ]);

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'birthDate' => optional($user->birth_date)?->toDateString(),
            'role' => $user->role,
            'email' => $user->email,
            'createdBy' => $user->created_by,
            'createdDate' => optional($user->created_date)?->toDateTimeString(),
            'updatedBy' => $user->updated_by,
            'updatedDate' => optional($user->updated_date)?->toDateTimeString(),
        ], Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'birthDate' => optional($user->birth_date)?->toDateString(),
            'role' => $user->role,
            'email' => $user->email,
            'createdBy' => $user->created_by,
            'createdDate' => optional($user->created_date)?->toDateTimeString(),
            'updatedBy' => $user->updated_by,
            'updatedDate' => optional($user->updated_date)?->toDateTimeString(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'birthDate' => 'nullable|date',
            'role' => 'sometimes|required|string|max:100',
            'email' => 'nullable|email|max:255',
            'password' => 'nullable|string|min:6',
            'updatedBy' => 'nullable|integer',
            'updatedDate' => 'nullable|date',
        ]);

        $user->fill([
            'name' => $data['name'] ?? $user->name,
            'birth_date' => array_key_exists('birthDate', $data) ? $data['birthDate'] : $user->birth_date,
            'role' => $data['role'] ?? $user->role,
            'email' => $data['email'] ?? $user->email,
            'password' => $data['password'] ?? $user->password,
            'updated_by' => $data['updatedBy'] ?? $user->updated_by,
            'updated_date' => $data['updatedDate'] ?? now(),
        ]);

        $user->save();

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'birthDate' => optional($user->birth_date)?->toDateString(),
            'role' => $user->role,
            'email' => $user->email,
            'createdBy' => $user->created_by,
            'createdDate' => optional($user->created_date)?->toDateTimeString(),
            'updatedBy' => $user->updated_by,
            'updatedDate' => optional($user->updated_date)?->toDateTimeString(),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $user->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
