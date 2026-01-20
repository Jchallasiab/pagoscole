@extends('template.main')

@section('title','REPORTE DE PAGOS POR GRADO Y SECCIÓN')

@section('content')
<div class="content-wrapper">

    <div class="content-header text-center">
        <h1 class="font-weight-bold">@yield('title')</h1>
    </div>

    <div class="content container-fluid">

        {{-- FILTROS --}}
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row">

                    <div class="col-md-3">
                        <label>Nivel</label>
                        <select id="level" name="level_id" class="form-control" required>
                            <option value="">-- Seleccione --</option>
                            @foreach($levels as $l)
                                <option value="{{ $l->id }}" {{ $level_id==$l->id?'selected':'' }}>
                                    {{ $l->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label>Grado</label>
                        <select id="grade" name="grade_id" class="form-control" required>
                            <option value="">-- Seleccione --</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label>Sección</label>
                        <select id="section" name="section_id" class="form-control" required>
                            <option value="">-- Seleccione --</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label>Año</label>
                        <select name="year" class="form-control">
                            @for($y = now()->year; $y >= 2020; $y--)
                                <option value="{{ $y }}" {{ $year==$y?'selected':'' }}>
                                    {{ $y }}
                                </option>
                            @endfor
                        </select>
                    </div>

                    <div class="col-md-1 d-flex align-items-end">
                        <button class="btn btn-primary w-100">
                            <i class="fa fa-search"></i>
                        </button>
                    </div>

                </form>
            </div>
        </div>

        {{-- BOTÓN EXCEL --}}
        @if(!$enrollments->isEmpty())
            <a href="{{ route('reports.payments.excel', request()->query()) }}"
               class="btn btn-success mb-3">
                <i class="fa fa-file-excel"></i> Exportar Excel
            </a>
        @endif

        {{-- TABLA --}}
        @if($enrollments->isEmpty())
            <div class="alert alert-info text-center">
                Seleccione nivel, grado y sección para ver el reporte.
            </div>
        @else
            <div class="card">
                <div class="card-body table-responsive">

                    <table class="table table-bordered text-center">
                        <thead class="thead-dark">
                            <tr>
                                <th>#</th>
                                <th>Estudiante</th>
                                @foreach($meses as $m)
                                    <th>{{ $m }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($enrollments as $i => $enrollment)
                                <tr>
                                    <td>{{ $i+1 }}</td>
                                    <td class="text-left">
                                        {{ $enrollment->student->nombres }}
                                        {{ $enrollment->student->apellido_paterno }}
                                        {{ $enrollment->student->apellido_materno }}
                                    </td>

                                    @foreach(range(1,12) as $mes)
                                        @php
                                            $periodo = sprintf('%04d-%02d',$year,$mes);
                                            $pagado = $enrollment->payments->firstWhere('periodo',$periodo);
                                        @endphp
                                        <td>
                                            @if($pagado)
                                                <span class="badge badge-success">✔</span>
                                            @else
                                                <span class="badge badge-danger">✘</span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                </div>
            </div>
        @endif

    </div>
</div>

{{-- FILTROS DEPENDIENTES SIN AJAX --}}
<script>
const grades = @json($gradesAll);
const sections = @json($sectionsAll);

const level   = document.getElementById('level');
const grade   = document.getElementById('grade');
const section = document.getElementById('section');

function loadGrades(levelId, selected=null){
    grade.innerHTML = '<option value="">-- Seleccione --</option>';
    section.innerHTML = '<option value="">-- Seleccione --</option>';

    grades.filter(g => g.level_id == levelId)
          .forEach(g => {
              grade.innerHTML += `<option value="${g.id}" ${g.id==selected?'selected':''}>${g.nombre}</option>`;
          });
}

function loadSections(gradeId, selected=null){
    section.innerHTML = '<option value="">-- Seleccione --</option>';

    sections.filter(s => s.grade_id == gradeId)
            .forEach(s => {
                section.innerHTML += `<option value="${s.id}" ${s.id==selected?'selected':''}>${s.nombre}</option>`;
            });
}

level.addEventListener('change', () => loadGrades(level.value));
grade.addEventListener('change', () => loadSections(grade.value));

@if($level_id)
    loadGrades({{ $level_id }}, {{ $grade_id ?? 'null' }});
@endif

@if($grade_id)
    loadSections({{ $grade_id }}, {{ $section_id ?? 'null' }});
@endif
</script>
@endsection
