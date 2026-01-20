<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StudentController extends Controller
{
    /** LISTAR ESTUDIANTES */
    public function index(Request $request)
    {
        $query = Student::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('dni', 'like', "%$search%")
                ->orWhere('nombres', 'like', "%$search%")
                ->orWhere('apellido_paterno', 'like', "%$search%")
                ->orWhere('apellido_materno', 'like', "%$search%");
        }

        $students = $query->orderBy('nombres')->paginate(10);

        return view('students.index', compact('students'));
    }

    /** FORMULARIO CREAR */
    public function create()
    {
        return view('students.create');
    }

    /** GUARDAR ESTUDIANTE */
    public function store(Request $request)
    {
        $data = $request->validate([
            'dni' => 'required|digits:8|unique:students,dni',
            'nombres' => 'required|string|max:100',
            'apellido_paterno' => 'required|string|max:100',
            'apellido_materno' => 'required|string|max:100',
            'email' => 'nullable|email|max:100',
            'celular' => 'required|digits:9',
            'direccion' => 'required|string|max:150',
            'nombre_apoderado' => 'nullable|string|max:150',
            'celular_apoderado' => 'nullable|digits:9',
            'photo' => 'nullable|image|max:2048', // JPG PNG máx 2MB
        ]);

        // 📸 Subir foto si existe
        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')
                ->store('students', 'public');
        }

        Student::create($data);

        return redirect()->route('students.index')
            ->with('success', 'Estudiante registrado correctamente.');
    }

    /** VER ESTUDIANTE */
    public function show(Student $student)
    {
        return view('students.show', compact('student'));
    }

    /** FORMULARIO EDITAR */
    public function edit(Student $student)
    {
        return view('students.edit', compact('student'));
    }

    /** ACTUALIZAR ESTUDIANTE */
    public function update(Request $request, Student $student)
    {
        $data = $request->validate([
            'dni' => 'required|digits:8|unique:students,dni,' . $student->id,
            'nombres' => 'required|string|max:100',
            'apellido_paterno' => 'required|string|max:100',
            'apellido_materno' => 'required|string|max:100',
            'email' => 'nullable|email|max:100',
            'celular' => 'required|digits:9',
            'direccion' => 'required|string|max:150',
            'nombre_apoderado' => 'nullable|string|max:150',
            'celular_apoderado' => 'nullable|digits:9',
            'photo' => 'nullable|image|max:2048',
        ]);

        // 📸 Reemplazar foto
        if ($request->hasFile('photo')) {
            if ($student->photo_path) {
                Storage::disk('public')->delete($student->photo_path);
            }

            $data['photo_path'] = $request->file('photo')
                ->store('students', 'public');
        }

        $student->update($data);

        return redirect()->route('students.index')
            ->with('success', 'Estudiante actualizado correctamente.');
    }

    /** ELIMINAR ESTUDIANTE */
    public function destroy(Student $student)
    {
        if ($student->photo_path) {
            Storage::disk('public')->delete($student->photo_path);
        }

        $student->delete();

        return redirect()->route('students.index')
            ->with('success', 'Estudiante eliminado correctamente.');
    }
}
