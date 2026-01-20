@extends('template.main')
@section('title', 'GRADOS')

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
                    <h5 class="mb-0">Listado de Grados</h5>
                    <a href="{{ route('grades.create') }}" class="btn btn-success">
                        <i class="fa fa-plus"></i> Nuevo Grado
                    </a>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped text-center align-middle">
                            <thead class="thead-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Nivel</th>
                                    <th>Grado</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($grades as $g)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $g->level->nombre }}</td>
                                    <td>{{ $g->nombre }}</td>
                                    <td>
                                        <span class="badge {{ $g->activo ? 'badge-success' : 'badge-secondary' }}">
                                            {{ $g->activo ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('grades.edit', $g->id) }}"
                                           class="btn btn-warning btn-sm">
                                            <i class="fa fa-pen"></i>
                                        </a>

                                        <form action="{{ route('grades.destroy', $g->id) }}"
                                              method="POST" style="display:inline-block;">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-danger btn-sm"
                                                onclick="return confirm('¿Eliminar grado?')">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

        </div>
    </div>
</div>
@endsection
