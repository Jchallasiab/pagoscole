@extends('template.main')
@section('title', 'NIVELES')

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
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between">
                    <h5 class="mb-0 text-primary">Listado de Niveles</h5>
                    <a href="{{ route('levels.create') }}" class="btn btn-success">
                        <i class="fa fa-plus"></i> Nuevo Nivel
                    </a>
                </div>

                <div class="card-body">
                    <table class="table table-bordered table-striped text-center">
                        <thead class="thead-dark">
                            <tr>
                                <th>#</th>
                                <th>Nombre</th>
                                <th>Activo</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($levels as $level)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $level->nombre }}</td>
                                <td>
                                    <span class="badge {{ $level->activo ? 'badge-success' : 'badge-secondary' }}">
                                        {{ $level->activo ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('levels.edit', $level->id) }}"
                                       class="btn btn-warning btn-sm">
                                        <i class="fa fa-pen"></i>
                                    </a>

                                    <form action="{{ route('levels.destroy', $level->id) }}"
                                          method="POST" style="display:inline-block;">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-danger btn-sm"
                                                onclick="return confirm('¿Eliminar nivel?')">
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
@endsection
