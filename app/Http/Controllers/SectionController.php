<?php

namespace App\Http\Controllers;

use App\Models\Section;
use App\Models\Grade;
use App\Models\SchoolYear;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SectionController extends Controller
{
    /** ================= LISTAR ================= */
    public function index()
    {
        $sections = Section::with(['schoolYear', 'grade.level'])
            ->orderBy('school_year_id', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(5); // 🔹 Muestra 5 por página

        return view('sections.index', compact('sections'));
    }

    /** ================= FORM CREAR ================= */
    public function create()
    {
        $schoolYears = SchoolYear::where('activo', true)
            ->orderBy('nombre', 'desc')
            ->get();

        $grades = Grade::with('level')
            ->where('activo', true)
            ->orderBy('level_id')
            ->orderBy('nombre')
            ->get();

        return view('sections.create', compact('grades', 'schoolYears'));
    }

    /** ================= GUARDAR ================= */
    public function store(Request $request)
    {
        $request->merge([
            'nombre' => strtoupper($request->nombre),
            'activo' => $request->has('activo'),
        ]);

        $request->validate([
            'school_year_id' => ['required', 'exists:school_years,id'],
            'grade_id' => ['required', 'exists:grades,id'],
            'nombre' => [
                'required',
                'string',
                'max:10',
                Rule::unique('sections')->where(function ($q) use ($request) {
                    return $q->where('school_year_id', $request->school_year_id)
                             ->where('grade_id', $request->grade_id);
                }),
            ],
            'capacidad' => ['nullable', 'integer', 'min:1'],
            'activo' => ['required', 'boolean'],
        ], [
            'nombre.unique' => 'Esta sección ya existe en este grado y año.',
        ]);

        Section::create($request->all());

        return redirect()
            ->route('sections.index')
            ->with('success', 'Sección creada correctamente.');
    }

    /** ================= FORM EDITAR ================= */
    public function edit(Section $section)
    {
        $schoolYears = SchoolYear::orderBy('nombre', 'desc')->get();

        $grades = Grade::with('level')
            ->where('activo', true)
            ->orderBy('level_id')
            ->orderBy('nombre')
            ->get();

        return view('sections.edit', compact('section', 'grades', 'schoolYears'));
    }

    /** ================= ACTUALIZAR ================= */
    public function update(Request $request, Section $section)
    {
        $request->merge([
            'nombre' => strtoupper($request->nombre),
            'activo' => $request->has('activo'),
        ]);

        $request->validate([
            'school_year_id' => ['required', 'exists:school_years,id'],
            'grade_id' => ['required', 'exists:grades,id'],
            'nombre' => [
                'required',
                'string',
                'max:10',
                Rule::unique('sections')
                    ->where(function ($q) use ($request) {
                        return $q->where('school_year_id', $request->school_year_id)
                                 ->where('grade_id', $request->grade_id);
                    })
                    ->ignore($section->id),
            ],
            'capacidad' => ['nullable', 'integer', 'min:1'],
            'activo' => ['required', 'boolean'],
        ]);

        $section->update($request->all());

        return redirect()
            ->route('sections.index')
            ->with('success', 'Sección actualizada correctamente.');
    }

    /** ================= ELIMINAR ================= */
    public function destroy(Section $section)
    {
        $section->delete();

        return redirect()
            ->route('sections.index')
            ->with('success', 'Sección eliminada correctamente.');
    }
}
