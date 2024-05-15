<?php
// app/Http/Controllers/AdminUserController.php

namespace App\Http\Controllers;

use App\Models\AdminUser;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    public function index()
    {
        return AdminUser::all();
    }

    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:admin_users,email',
            'password' => 'required',
            'username' => 'required|unique:admin_users,username',
        ]);

        $adminUser = AdminUser::create([
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'username' => $request->username,
        ]);

        return response()->json($adminUser, 201);
    }

    public function show(AdminUser $adminUser)
    {
        return $adminUser;
    }

    public function update(Request $request, AdminUser $adminUser)
    {
        $request->validate([
            'email' => 'required|email|unique:admin_users,email,' . $adminUser->id,
            'username' => 'required|unique:admin_users,username,' . $adminUser->id,
        ]);

        $adminUser->update($request->all());

        return response()->json($adminUser, 200);
    }

    public function destroy(AdminUser $adminUser)
    {
        $adminUser->delete();

        return response()->json(null, 204);
    }
}
