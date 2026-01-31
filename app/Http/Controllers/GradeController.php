<?php

namespace App\Http\Controllers;

use App\Models\Grade;
use App\Models\Level;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class GradeController extends Controller
{
    /** ================= LISTAR ================= */
    public function index()
    {
        $grades = Grade::with('level')
            ->orderBy('level_id')
            ->orderBy('nombre')
            ->paginate(5); // 🔹 Solo 5 registros por página

        return view('grades.index', compact('grades'));
    }

    /** ================= FORMULARIO CREAR ================= */
    public function create()
    {
        $levels = Level::where('activo', true)
            ->orderBy('nombre')
            ->get();

        return view('grades.create', compact('levels'));
    }

    /** ================= GUARDAR ================= */
    public function store(Request $request)
    {
        $request->validate([
            'level_id' => ['required', 'exists:levels,id'],
            'nombre'   => [
                'required',
                'string',
                'max:50',
                Rule::unique('grades')
                    ->where(fn ($q) => $q->where('level_id', $request->level_id)),
            ],
        ], [
            'nombre.unique' => 'Este grado ya existe en el nivel seleccionado.',
        ]);

        Grade::create([
            'level_id' => $request->level_id,
            'nombre'   => strtoupper(trim($request->nombre)), // 🔹 Normalizamos texto
            'activo'   => $request->has('activo'),
        ]);

        return redirect()
            ->route('grades.index')
            ->with('success', 'Grado creado correctamente.');
    }

    /** ================= FORMULARIO EDITAR ================= */
    public function edit(Grade $grade)
    {
        $levels = Level::where('activo', true)
            ->orderBy('nombre')
            ->get();

        return view('grades.edit', compact('grade', 'levels'));
    }

    /** ================= ACTUALIZAR ================= */
    public function update(Request $request, Grade $grade)
    {
        $request->validate([
            'level_id' => ['required', 'exists:levels,id'],
            'nombre'   => [
                'required',
                'string',
                'max:50',
                Rule::unique('grades')
                    ->where(fn ($q) => $q->where('level_id', $request->level_id))
                    ->ignore($grade->id),
            ],
        ], [
            'nombre.unique' => 'Este grado ya existe en el nivel seleccionado.',
        ]);

        $grade->update([
            'level_id' => $request->level_id,
            'nombre'   => strtoupper(trim($request->nombre)),
            'activo'   => $request->has('activo'),
        ]);

        return redirect()
            ->route('grades.index')
            ->with('success', 'Grado actualizado correctamente.');
    }

    /** ================= ELIMINAR ================= */
    public function destroy(Grade $grade)
    {
        $grade->delete();

        return redirect()
            ->route('grades.index')
            ->with('success', 'Grado eliminado correctamente.');
    }
}
