@extends('template.main')
@section('title', 'EDITAR MATRÍCULA')

@section('content')
<div class="content-wrapper">

    <!-- ENCABEZADO -->
    <div class="content-header">
        <div class="container-fluid text-center">
            <h1 class="m-0"><strong>@yield('title')</strong></h1>
        </div>
    </div>

    <!-- CONTENIDO -->
    <div class="content">
        <div class="container-fluid">

            <div class="card shadow-sm">
                <div class="card-body">

                    <form action="{{ route('enrollments.update', $enrollment->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- FILA 1: Estudiante + Año Escolar -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label><strong>ESTUDIANTE:</strong></label>
                                <select name="student_id" class="form-control select2" required>
                                    <option value="">Seleccione un estudiante</option>
                                    @foreach($students as $s)
                                        <option value="{{ $s->id }}"
                                            {{ old('student_id', $enrollment->student_id) == $s->id ? 'selected' : '' }}>
                                            {{ $s->dni }} - {{ $s->apellido_paterno }} {{ $s->apellido_materno }}, {{ $s->nombres }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('student_id')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label><strong>AÑO ESCOLAR:</strong></label>
                                <select id="school_year_id" class="form-control" required>
                                    <option value="">Seleccione año</option>
                                    @foreach($schoolYears as $sy)
                                        <option value="{{ $sy->id }}"
                                            {{ old('school_year_id', $enrollment->school_year_id) == $sy->id ? 'selected' : '' }}>
                                            {{ $sy->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <hr>

                        <!-- FILA 2: Nivel + Grado -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label><strong>NIVEL:</strong></label>
                                <select id="level_id" class="form-control" required>
                                    <option value="">Seleccione nivel</option>
                                    @foreach($levels as $l)
                                        <option value="{{ $l->id }}"
                                            {{ old('level_id', $enrollment->level_id) == $l->id ? 'selected' : '' }}>
                                            {{ $l->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label><strong>GRADO:</strong></label>
                                <select id="grade_id" name="grade_id" class="form-control" required>
                                    <option value="">Seleccione grado</option>
                                    @foreach($grades as $g)
                                        <option value="{{ $g->id }}"
                                            data-level="{{ $g->level_id }}"
                                            {{ old('grade_id', $enrollment->grade_id) == $g->id ? 'selected' : '' }}>
                                            {{ $g->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('grade_id')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                        </div>

                        <!-- FILA 3: Sección + Fecha -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label><strong>SECCIÓN:</strong></label>
                                <select id="section_id" name="section_id" class="form-control" required>
                                    <option value="">Seleccione sección</option>
                                    @foreach($sections as $s)
                                        <option value="{{ $s->id }}"
                                            {{ old('section_id', $enrollment->section_id) == $s->id ? 'selected' : '' }}>
                                            {{ $s->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('section_id')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label><strong>FECHA MATRÍCULA:</strong></label>
                                <input type="date" name="fecha_matricula" class="form-control"
                                       value="{{ old('fecha_matricula', $enrollment->fecha_matricula) }}" required>
                                @error('fecha_matricula')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                        </div>

                        <!-- FILA 4: Monto + Estado -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label><strong>MONTO MATRÍCULA (S/):</strong></label>
                                <input type="number" name="monto_matricula" class="form-control" step="0.01"
                                       value="{{ old('monto_matricula', $enrollment->monto_matricula) }}" required>
                                @error('monto_matricula')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label><strong>ESTADO:</strong></label>
                                <select name="estado" class="form-control" required>
                                    <option value="pendiente" {{ old('estado', $enrollment->estado) == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                                    <option value="pagado" {{ old('estado', $enrollment->estado) == 'pagado' ? 'selected' : '' }}>Pagado</option>
                                    <option value="validado" {{ old('estado', $enrollment->estado) == 'validado' ? 'selected' : '' }}>Validado</option>
                                </select>
                                @error('estado')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                        </div>

                        <!-- INPUTS OCULTOS -->
                        <input type="hidden" name="school_year_id" id="hidden_year" value="{{ $enrollment->school_year_id }}">
                        <input type="hidden" name="level_id" id="hidden_level" value="{{ $enrollment->level_id }}">

                        <!-- BOTONES -->
                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="fa-solid fa-save"></i> ACTUALIZAR MATRÍCULA
                            </button>
                            <a href="{{ route('enrollments.index') }}" class="btn btn-secondary px-4">
                                <i class="fa-solid fa-arrow-left"></i> VOLVER
                            </a>
                        </div>

                    </form>

                </div>
            </div>

        </div>
    </div>
</div>

<!-- JS -->
<script>
const year    = document.getElementById('school_year_id');
const level   = document.getElementById('level_id');
const grade   = document.getElementById('grade_id');
const section = document.getElementById('section_id');

const hiddenYear  = document.getElementById('hidden_year');
const hiddenLevel = document.getElementById('hidden_level');

/* PRE-CARGA */
document.addEventListener('DOMContentLoaded', () => {
    filtrarGrados();
});

/* FILTRAR GRADOS */
function filtrarGrados() {
    hiddenLevel.value = level.value;
    [...grade.options].forEach(o => {
        if (!o.dataset.level) return;
        o.hidden = o.dataset.level !== level.value;
    });
}

level.addEventListener('change', () => {
    filtrarGrados();
    grade.value = '';
    section.innerHTML = '<option>Cargando...</option>';
});

/* CARGAR SECCIONES */
grade.addEventListener('change', cargarSecciones);
year.addEventListener('change', cargarSecciones);

function cargarSecciones() {
    hiddenYear.value = year.value;

    if (!year.value || !grade.value) return;

    section.innerHTML = '<option>Cargando...</option>';

    fetch(`{{ route('enrollments.sections') }}?school_year_id=${year.value}&grade_id=${grade.value}`)
        .then(r => r.json())
        .then(data => {
            section.innerHTML = '';
            if (data.length === 0) {
                section.innerHTML = '<option>No hay secciones</option>';
            } else {
                data.forEach(s => {
                    section.innerHTML += `<option value="${s.id}">${s.nombre}</option>`;
                });
            }
        })
        .catch(() => {
            section.innerHTML = '<option>Error al cargar</option>';
        });
}
</script>
@endsection
