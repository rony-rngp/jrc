<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Admin;
use App\Helpers\ActivityLogger;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = Admin::orderBy('id', 'desc')->get();
        return view('users.index', compact('users'));
    }

    public function create()
    {
        return redirect()->route('users.index');
    }

    public function store(Request $request)
    {
        $request->validate([
            'username' => 'required|unique:admin,username',
            'password' => 'required|min:4',
            'firstname' => 'required',
        ]);

        $photoName = '';
        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $photoName = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('images'), $photoName);
        }

        $user = Admin::create([
            'username' => $request->input('username'),
            'password' => Hash::make($request->input('password')),
            'firstname' => $request->input('firstname'),
            'lastname' => $request->input('lastname', ''),
            'photo' => $photoName ?: 'profile.jpg',
            'role' => (int)$request->input('role', 3),
            'empID' => (int)$request->input('empID', 0),
            'created_on' => date('Y-m-d'),
        ]);

        ActivityLogger::log('Create', "Added user {$user->username}");

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    public function show($id)
    {
        $user = Admin::findOrFail($id);
        $logs = \App\Models\ActivityLog::where('user_id', $user->id)
            ->orWhere('description', 'like', "%{$user->username}%")
            ->orderBy('id', 'desc')
            ->limit(50)
            ->get();

        return view('users.show', compact('user', 'logs'));
    }

    public function edit($id)
    {
        $user = Admin::findOrFail($id);
        $users = Admin::orderBy('id', 'desc')->get();
        return view('users.index', compact('users', 'user'));
    }

    public function update(Request $request, $id)
    {
        $user = Admin::findOrFail($id);

        $data = [
            'firstname' => $request->input('firstname'),
            'lastname' => $request->input('lastname'),
            'role' => (int)$request->input('role', 3),
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->input('password'));
        }

        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $photoName = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('images'), $photoName);
            $data['photo'] = $photoName;
        }

        $user->update($data);
        ActivityLogger::log('Update', "Updated user {$user->username}");

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    public function destroy($id)
    {
        if ($id == 1) {
            return redirect()->back()->with('error', 'Cannot delete super admin!');
        }

        $user = Admin::findOrFail($id);
        $name = $user->username;
        $user->delete();

        ActivityLogger::log('Delete', "Deleted user {$name}");

        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return redirect()->route('login');
        }

        $data = [
            'firstname' => $request->input('firstname', $user->firstname),
            'lastname' => $request->input('lastname', $user->lastname),
        ];

        if ($request->filled('username')) {
            $data['username'] = $request->input('username');
        }

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->input('password'));
        }

        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $photoName = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('images'), $photoName);
            $data['photo'] = $photoName;
        }

        $user->update($data);
        ActivityLogger::log('Update', "Profile updated for {$user->username}");

        return redirect()->back()->with('success', 'Profile updated successfully.');
    }
}
