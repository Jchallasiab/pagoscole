@extends('template.main')
@section('title', 'AÑOS ESCOLARES')

@section('content')
<div class="content-wrapper">

    <div class="content-header">
        <div class="container-fluid">
            <h1><strong>@yield('title')</strong></h1>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between">
                    <h5 class="mb-0 text-primary">Listado de Años Escolares</h5>
                    <a href="{{ route('school_years.create') }}"
                       class="btn btn-success">
                        <i class="fa fa-plus"></i> Nuevo Año
                    </a>
                </div>

                <div class="card-body">
                    @if($school_years->isEmpty())
                        <p class="text-center text-muted">No hay años escolares registrados.</p>
                    @else
                        <table class="table table-bordered text-center">
                            <thead class="thead-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Nombre</th>
                                    <th>Inicio</th>
                                    <th>Fin</th>
                                    <th>Activo</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($school_years as $sy)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $sy->nombre }}</td>
                                    <td>{{ $sy->fecha_inicio ?? '-' }}</td>
                                    <td>{{ $sy->fecha_fin ?? '-' }}</td>
                                    <td>
                                        <span class="badge {{ $sy->activo ? 'badge-success' : 'badge-secondary' }}">
                                            {{ $sy->activo ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('school_years.edit', $sy->id) }}"
                                           class="btn btn-warning btn-sm">
                                            <i class="fa fa-pen"></i>
                                        </a>

                                        <form action="{{ route('school_years.destroy', $sy->id) }}"
                                              method="POST" style="display:inline-block;">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-danger btn-sm"
                                                    onclick="return confirm('¿Eliminar año escolar?')">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>

            </div>
        </div>
    </div>
</div>
@endsection
