@extends('template.main')
@section('title', 'MATRICULADOS')
@section('content')

<div class="content-wrapper">

    <!-- ENCABEZADO -->
    <div class="content-header">
        <div class="container-fluid">
            <h1 class="m-0"><strong>@yield('title')</strong></h1>
        </div>
    </div>

    <!-- CONTENIDO -->
    <div class="content">
        <div class="container-fluid">

            {{-- MENSAJE DE ÉXITO --}}
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fa-solid fa-check-circle"></i> {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
            @endif

            <div class="card shadow-sm">

                <!-- CABECERA -->
                <div class="card-header d-flex justify-content-between flex-wrap align-items-center">

                    <!-- BUSCADOR -->
                    <form method="GET" action="{{ route('enrollments.index') }}" class="form-inline mb-2">
                        <input type="text" name="search"
                               value="{{ request('search') }}"
                               class="form-control mr-2"
                               placeholder="Buscar por DNI o nombre">

                        <button class="btn btn-primary mr-2">
                            <i class="fa-solid fa-magnifying-glass"></i> Buscar
                        </button>

                        @if(request()->has('search') || request()->hasAny(['school_year_id','level_id','grade_id','section_id']))
                            <a href="{{ route('enrollments.index') }}" class="btn btn-secondary">
                                Limpiar
                            </a>
                        @endif
                    </form>

                    <!-- BOTONES -->
                    <div class="mb-2">
                        <button class="btn btn-success" data-toggle="modal" data-target="#modalFiltro">
                            <i class="fa-solid fa-filter"></i> FILTRAR
                        </button>

                        <a href="{{ route('enrollments.export.excel', request()->query()) }}"
                            class="btn btn-success ml-2">
                            <i class="fa-solid fa-file-excel"></i> EXPORTAR EXCEL
                        </a>


                        <a href="{{ route('enrollments.create') }}" class="btn btn-primary ml-2">
                            <i class="fa-solid fa-plus"></i> MATRICULAR
                        </a>
                    </div>

                </div>

                <!-- TABLA -->
                <div class="card-body">
                    @if($enrollments->isEmpty())
                        <div class="text-center text-muted">
                            <i class="fa-solid fa-user-graduate fa-2x mb-2"></i>
                            <p>No hay estudiantes matriculados.</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-hover text-center align-middle">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>DNI</th>
                                        <th>ESTUDIANTE</th>
                                        <th>NIVEL</th>
                                        <th>GRADO</th>
                                        <th>SECCIÓN</th>
                                        <th>AÑO ESCOLAR</th>
                                        <th>FECHA MATRÍCULA</th>
                                        <th>ESTADO</th>
                                        <th>ACCIONES</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($enrollments as $e)
                                        <tr>
                                            <td>{{ $loop->iteration + ($enrollments->currentPage()-1)*$enrollments->perPage() }}</td>
                                            <td>{{ $e->student->dni }}</td>
                                            <td>{{ $e->student->nombres }} {{ $e->student->apellido_paterno }} {{ $e->student->apellido_materno }}</td>
                                            <td>{{ $e->level->nombre ?? '-' }}</td>
                                            <td>{{ $e->grade->nombre ?? '-' }}</td>
                                            <td>{{ $e->section->nombre ?? '-' }}</td>
                                            <td>{{ $e->schoolYear->nombre ?? '-' }}</td>
                                            <td>{{ $e->fecha_matricula }}</td>
                                            <td>
                                                <span class="badge 
                                                    @if($e->estado == 'pagado') badge-success
                                                    @elseif($e->estado == 'pendiente') badge-warning
                                                    @else badge-info @endif">
                                                    {{ ucfirst($e->estado) }}
                                                </span>
                                            </td>
                                            <td>
                                                <a href="{{ route('enrollments.edit', $e->id) }}" class="btn btn-warning btn-sm mb-1" title="Editar">
                                                    <i class="fa-solid fa-pen"></i>
                                                </a>
                                                <a href="{{ route('enrollments.show', $e->id) }}"
                                                    class="btn btn-info btn-sm mb-1"
                                                    title="Ver matrícula">
                                                        <i class="fa-solid fa-eye"></i>
                                                    </a>


                                                @if($e->voucher_matricula)
                                                    <a href="{{ route('enrollments.voucher', $e->id) }}" target="_blank"
                                                       class="btn btn-info btn-sm mb-1" title="Ver PDF">
                                                        <i class="fa-solid fa-file-pdf"></i>
                                                    </a>
                                                @endif

                                                @if(auth()->user()->role === 'admin')
                                                    <form action="{{ route('enrollments.destroy', $e->id) }}" method="POST" style="display:inline-block;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger btn-sm mb-1"
                                                            onclick="return confirm('¿Seguro que deseas eliminar esta matrícula?')">
                                                            <i class="fa-solid fa-trash-can"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- PAGINACIÓN -->
                        <div class="d-flex justify-content-between align-items-center mt-2 flex-wrap">
                            <small>
                                Mostrando {{ $enrollments->firstItem() }}
                                a {{ $enrollments->lastItem() }}
                                de {{ $enrollments->total() }} registros
                            </small>
                            {{ $enrollments->appends(request()->query())->links('pagination::bootstrap-4') }}
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>

<!-- MODAL FILTROS -->
<div class="modal fade" id="modalFiltro" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fa-solid fa-filter"></i> Filtrar matrículas
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>

            <form method="GET" action="{{ route('enrollments.index') }}">
                <div class="modal-body">

                    <!-- AÑO -->
                    <div class="form-group">
                        <label><strong>Año Escolar</strong></label>
                        <select id="f_year" name="school_year_id" class="form-control">
                            <option value="">-- Todos --</option>
                            @foreach($years as $y)
                                <option value="{{ $y->id }}" {{ request('school_year_id') == $y->id ? 'selected' : '' }}>
                                    {{ $y->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- NIVEL -->
                    <div class="form-group">
                        <label><strong>Nivel</strong></label>
                        <select id="f_level" name="level_id" class="form-control">
                            <option value="">-- Todos --</option>
                            @foreach($levels as $l)
                                <option value="{{ $l->id }}" {{ request('level_id') == $l->id ? 'selected' : '' }}>
                                    {{ $l->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- GRADO -->
                    <div class="form-group">
                        <label><strong>Grado</strong></label>
                        <select id="f_grade" name="grade_id" class="form-control">
                            <option value="">-- Todos --</option>
                            @foreach($grades as $g)
                                <option value="{{ $g->id }}"
                                    data-level="{{ $g->level_id }}"
                                    {{ request('grade_id') == $g->id ? 'selected' : '' }}>
                                    {{ $g->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- SECCIÓN -->
                    <div class="form-group">
                        <label><strong>Sección</strong></label>
                        <select id="f_section" name="section_id" class="form-control">
                            <option value="">-- Todas --</option>
                        </select>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">
                        <i class="fa-solid fa-search"></i> FILTRAR
                    </button>
                    <a href="{{ route('enrollments.index') }}" class="btn btn-secondary">
                        LIMPIAR
                    </a>
                </div>
            </form>

        </div>
    </div>
</div>
<script>
const fYear    = document.getElementById('f_year');
const fLevel   = document.getElementById('f_level');
const fGrade   = document.getElementById('f_grade');
const fSection = document.getElementById('f_section');

/* FILTRAR GRADOS POR NIVEL */
function filtrarGradosFiltro() {
    [...fGrade.options].forEach(opt => {
        if (!opt.dataset.level) return;
        opt.hidden = fLevel.value && opt.dataset.level !== fLevel.value;
    });
}

/* CARGAR SECCIONES POR AÑO + GRADO */
function cargarSeccionesFiltro() {
    if (!fYear.value || !fGrade.value) {
        fSection.innerHTML = '<option value="">-- Todas --</option>';
        return;
    }

    fSection.innerHTML = '<option>Cargando...</option>';

    fetch(`{{ route('enrollments.sections') }}?school_year_id=${fYear.value}&grade_id=${fGrade.value}`)
        .then(r => r.json())
        .then(data => {
            fSection.innerHTML = '<option value="">-- Todas --</option>';
            data.forEach(s => {
                fSection.innerHTML += `<option value="${s.id}">${s.nombre}</option>`;
            });
        });
}

/* EVENTOS */
fLevel.addEventListener('change', () => {
    filtrarGradosFiltro();
    fGrade.value = '';
    fSection.innerHTML = '<option value="">-- Todas --</option>';
});

fGrade.addEventListener('change', cargarSeccionesFiltro);
fYear.addEventListener('change', cargarSeccionesFiltro);

/* PRE-CARGA SI HAY FILTROS */
document.addEventListener('DOMContentLoaded', () => {
    filtrarGradosFiltro();
    cargarSeccionesFiltro();
});
</script>
<script>
@if(session('voucher_id'))
    // Esperar un momento para no interrumpir el mensaje de éxito
    setTimeout(() => {
        const voucherUrl = "{{ route('enrollments.voucher', session('voucher_id')) }}";
        window.open(voucherUrl, '_blank'); // 🔥 abre PDF en nueva pestaña
    }, 800);
@endif
</script>


@endsection
