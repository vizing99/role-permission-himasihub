<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest; // Pastikan Anda memiliki form request yang sesuai
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UsersController extends Controller
{
    public function index(): Renderable
    {
        $this->checkAuthorization(auth()->user(), ['user.view']);

        return view('backend.pages.users.index', [
            'users' => User::all(),
        ]);
    }

    public function create(): Renderable
    {
        $this->checkAuthorization(auth()->user(), ['user.create']);

        return view('backend.pages.users.create');
    }

    public function store(UserRequest $request)
{
    // Validasi sudah terjadi di UserRequest
    $validated = $request->validated();

    $user = new User();
    $user->name = $validated['name'];
    $user->email = $validated['email'];
    $user->username = $validated['username'];
    $user->password = Hash::make($validated['password']);
    $user->save();

    // Menetapkan role jika ada
    if ($request->roles) {
        $user->assignRole($request->roles);
    }

    session()->flash('success', 'User has been created.');
    return redirect()->route('admin.users.index');
}

    public function edit(int $id): Renderable
    {
        $this->checkAuthorization(auth()->user(), ['user.edit']);

        $user = User::findOrFail($id);
        return view('backend.pages.users.edit', [
            'user' => $user,
        ]);
    }

    public function update(UserRequest $request, int $id): RedirectResponse
    {
        $this->checkAuthorization(auth()->user(), ['user.edit']);

        $user = User::findOrFail($id);
        $user->name = $request->name;
        $user->email = $request->email;
        if ($request->password) {
            $user->password = Hash::make($request->password);
        }
        $user->save();

        session()->flash('success', 'User has been updated.');
        return back();
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->checkAuthorization(auth()->user(), ['user.delete']);

        $user = User::findOrFail($id);
        $user->delete();
        session()->flash('success', 'User has been deleted.');
        return back();
    }
}
