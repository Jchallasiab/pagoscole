<?php

namespace App\Http\Controllers;

use App\Models\Level;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LevelController extends Controller
{
    /** ================= LISTAR ================= */
    public function index()
    {
        $levels = Level::orderBy('nombre')->get();
        return view('levels.index', compact('levels'));
    }

    /** ================= FORMULARIO CREAR ================= */
    public function create()
    {
        return view('levels.create');
    }

    /** ================= GUARDAR ================= */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:50',
                'unique:levels,nombre'
            ],
            'activo' => ['nullable', 'boolean'],
        ], [
            'nombre.unique' => 'Este nivel ya existe.'
        ]);

        Level::create([
            'nombre' => $request->nombre,
            'activo' => $request->has('activo'),
        ]);

        return redirect()
            ->route('levels.index')
            ->with('success', 'Nivel creado correctamente.');
    }

    /** ================= FORMULARIO EDITAR ================= */
    public function edit(Level $level)
    {
        return view('levels.edit', compact('level'));
    }

    /** ================= ACTUALIZAR ================= */
    public function update(Request $request, Level $level)
    {
        $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:50',
                Rule::unique('levels', 'nombre')->ignore($level->id)
            ],
            'activo' => ['nullable', 'boolean'],
        ], [
            'nombre.unique' => 'Este nivel ya existe.'
        ]);

        $level->update([
            'nombre' => $request->nombre,
            'activo' => $request->has('activo'),
        ]);

        return redirect()
            ->route('levels.index')
            ->with('success', 'Nivel actualizado correctamente.');
    }

    /** ================= ELIMINAR ================= */
    public function destroy(Level $level)
    {
        $level->delete();

        return redirect()
            ->route('levels.index')
            ->with('success', 'Nivel eliminado correctamente.');
    }
}
