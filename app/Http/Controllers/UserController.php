<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /** ================= LISTAR ================= */
    public function index()
    {
        $users = User::orderBy('id_user', 'asc')->paginate(5);
        return view('users.index', compact('users'));
    }

    /** ================= FORM CREAR ================= */
    public function create()
    {
        return view('users.create');
    }

    /** ================= GUARDAR ================= */
    public function store(Request $request)
    {
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:users,name'
            ],
            'email' => [
                'required',
                'email',
                'unique:users,email'
            ],
            'password' => [
                'required',
                'min:6',
                'confirmed'
            ],
            'role' => [
                'required',
                'in:admin,secretaria'
            ],
        ], [
            'name.unique' => 'El nombre de usuario ya existe.',
            'email.unique' => 'El correo electrónico ya está registrado.',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        return redirect()
            ->route('users.index')
            ->with('success', 'Usuario creado correctamente.');
    }

    /** ================= FORM EDITAR ================= */
    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    /** ================= ACTUALIZAR ================= */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('users', 'name')->ignore($user->id_user, 'id_user'),
            ],
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($user->id_user, 'id_user'),
            ],
            'password' => [
                'nullable',
                'min:6',
                'confirmed'
            ],
            'role' => [
                'required',
                'in:admin,secretaria'
            ],
        ], [
            'name.unique' => 'El nombre de usuario ya existe.',
            'email.unique' => 'El correo electrónico ya está registrado.',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
        ];

        // 👉 Solo actualiza password si se envía
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()
            ->route('users.index')
            ->with('success', 'Usuario actualizado correctamente.');
    }

    /** ================= ELIMINAR ================= */
    public function destroy($id_user)
    {
        $user = User::findOrFail($id_user);

        if ($user->role === 'admin') {
            return back()->withErrors([
                'error' => 'No se puede eliminar otro administrador.'
            ]);
        }

        $user->delete();

        return back()->with('success', 'Usuario eliminado correctamente.');
    }
}
