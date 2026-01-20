@extends('template.main')
@section('title', 'ESTUDIANTES')
@section('content')

<div class="content-wrapper">
    <!-- Encabezado -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><strong>@yield('title')</strong></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="/">Inicio</a></li>
                        <li class="breadcrumb-item active">@yield('title')</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Contenido principal -->
    <div class="content">
        <div class="container-fluid">

            {{-- ALERTA --}}
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fa-solid fa-check-circle"></i> {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                    <h5 class="mb-0 text-primary"><strong>Listado de Estudiantes</strong></h5>
                    <a href="{{ route('students.create') }}" class="btn btn-success">
                        <i class="fa-solid fa-plus"></i> AGREGAR ESTUDIANTE
                    </a>
                </div>

                <div class="card-body">

                    {{-- BUSCADOR --}}
                    <form method="GET" action="{{ route('students.index') }}" class="form-inline mb-4 justify-content-center">
                        <input type="text" name="search" value="{{ request('search') }}"
                               class="form-control mr-2 w-50"
                               placeholder="Buscar por DNI, nombre o apellidos">
                        <button class="btn btn-primary mr-2">
                            <i class="fa-solid fa-magnifying-glass"></i> Buscar
                        </button>
                        @if(request('search'))
                            <a href="{{ route('students.index') }}" class="btn btn-secondary">
                                Limpiar
                            </a>
                        @endif
                    </form>

                    @if($students->isEmpty())
                        <div class="text-center text-muted">
                            <p><i class="fa-solid fa-user-graduate fa-2x mb-2"></i></p>
                            <p>No hay estudiantes registrados.</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover text-center align-middle">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>Foto</th>
                                        <th>DNI</th>
                                        <th>Nombres</th>
                                        <th>Apellidos</th>
                                        <th>Correo</th>
                                        <th>Celular</th>
                                        <th>Dirección</th>
                                        <th>Apoderado</th>
                                        <th>Cel. Apoderado</th>
                                        <th>Estado</th>
                                        <th>Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($students as $s)
                                        <tr>
                                            <td>{{ $loop->iteration + ($students->currentPage() - 1) * $students->perPage() }}</td>

                                            <td>
                                               @if($s->photo_path)
                                                    <img src="{{ route('mostrar.foto', basename($s->photo_path)) }}" alt="Foto"
                                                        class="rounded-circle" style="width:45px; height:45px; object-fit:cover;">
                                                @else
                                                    <img src="{{ asset('img/default-avatar.png') }}" alt="Sin foto"
                                                        class="rounded-circle" style="width:45px; height:45px; opacity:0.6;">
                                                @endif
                                            </td>

                                            <td>{{ $s->dni }}</td>
                                            <td>{{ $s->nombres }}</td>
                                            <td>{{ $s->apellido_paterno }} {{ $s->apellido_materno }}</td>
                                            <td>{{ $s->email ?? '—' }}</td>
                                            <td>{{ $s->celular }}</td>
                                            <td>{{ $s->direccion }}</td>
                                            <td>{{ $s->nombre_apoderado ?? '—' }}</td>
                                            <td>{{ $s->celular_apoderado ?? '—' }}</td>

                                            <td>
                                                @php
                                                    $badgeClass = match($s->estado) {
                                                        'activo' => 'badge-success',
                                                        'inactivo' => 'badge-secondary',
                                                        'retirado' => 'badge-danger',
                                                        default => 'badge-light'
                                                    };
                                                @endphp
                                                <span class="badge {{ $badgeClass }}">
                                                    {{ ucfirst($s->estado) }}
                                                </span>
                                            </td>

                                            <td>
                                                <!-- Botón VER -->
                                                <a href="{{ route('students.show', $s->id) }}" class="btn btn-info btn-sm mb-1" title="Ver detalles">
                                                    <i class="fa-solid fa-eye"></i>
                                                </a>

                                                <!-- Botón EDITAR -->
                                                <a href="{{ route('students.edit', $s->id) }}" class="btn btn-warning btn-sm mb-1" title="Editar">
                                                    <i class="fa-solid fa-pen"></i>
                                                </a>

                                                <!-- Botón ELIMINAR (solo admin) -->
                                                @if(auth()->user()->role === 'admin')
                                                    <form action="{{ route('students.destroy', $s->id) }}" method="POST" style="display:inline-block;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger btn-sm mb-1"
                                                            onclick="return confirm('¿Seguro que deseas eliminar este estudiante?')">
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

                        {{-- INFO + PAGINACIÓN --}}
                        <div class="d-flex justify-content-between align-items-center flex-wrap mt-3">
                            <small class="text-muted">
                                Mostrando del {{ $students->firstItem() }}
                                al {{ $students->lastItem() }}
                                de un total de {{ $students->total() }} registros
                            </small>
                            {{ $students->appends(request()->query())->links('pagination::bootstrap-4') }}
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
