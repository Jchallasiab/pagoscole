<?php

namespace App\Http\Controllers;

use App\Models\SchoolYear;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SchoolYearController extends Controller
{
    /** ================= LISTAR ================= */
    public function index()
    {
        $school_years = SchoolYear::orderBy('nombre', 'desc')->get();
        return view('school_years.index', compact('school_years'));
    }

    /** ================= FORMULARIO CREAR ================= */
    public function create()
    {
        return view('school_years.create');
    }

    /** ================= GUARDAR ================= */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:9',
                'unique:school_years,nombre',
            ],
            'fecha_inicio' => ['nullable', 'date'],
            'fecha_fin' => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
            'activo' => ['nullable', 'boolean'],
        ], [
            'nombre.unique' => 'Este año escolar ya existe.',
            'fecha_fin.after_or_equal' => 'La fecha fin debe ser mayor o igual a la fecha inicio.',
        ]);

        SchoolYear::create([
            'nombre' => $request->nombre,
            'fecha_inicio' => $request->fecha_inicio,
            'fecha_fin' => $request->fecha_fin,
            'activo' => $request->has('activo'),
        ]);

        return redirect()
            ->route('school_years.index')
            ->with('success', 'Año escolar creado correctamente.');
    }

    /** ================= FORMULARIO EDITAR ================= */
    public function edit(SchoolYear $school_year)
    {
        return view('school_years.edit', compact('school_year'));
    }

    /** ================= ACTUALIZAR ================= */
    public function update(Request $request, SchoolYear $school_year)
    {
        $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:9',
                Rule::unique('school_years', 'nombre')->ignore($school_year->id),
            ],
            'fecha_inicio' => ['nullable', 'date'],
            'fecha_fin' => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
            'activo' => ['nullable', 'boolean'],
        ], [
            'nombre.unique' => 'Este año escolar ya existe.',
            'fecha_fin.after_or_equal' => 'La fecha fin debe ser mayor o igual a la fecha inicio.',
        ]);

        $school_year->update([
            'nombre' => $request->nombre,
            'fecha_inicio' => $request->fecha_inicio,
            'fecha_fin' => $request->fecha_fin,
            'activo' => $request->has('activo'),
        ]);

        return redirect()
            ->route('school_years.index')
            ->with('success', 'Año escolar actualizado correctamente.');
    }

    /** ================= ELIMINAR ================= */
    public function destroy(SchoolYear $school_year)
    {
        $school_year->delete();

        return redirect()
            ->route('school_years.index')
            ->with('success', 'Año escolar eliminado correctamente.');
    }
}
