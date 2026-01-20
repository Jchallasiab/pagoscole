@extends('template.main')
@section('title', 'SECCIONES')

@section('content')
<div class="content-wrapper">

    <div class="content-header">
        <h1><strong>@yield('title')</strong></h1>
    </div>

    <div class="content">

        {{-- ALERT SUCCESS --}}
        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between">
                <h5 class="mb-0">Listado de Secciones</h5>
                <a href="{{ route('sections.create') }}" class="btn btn-success">
                    <i class="fa fa-plus"></i> Nueva Sección
                </a>
            </div>

            <div class="card-body table-responsive">
                <table class="table table-bordered table-striped text-center">
                    <thead class="thead-dark">
                        <tr>
                            <th>#</th>
                            <th>Año</th>
                            <th>Nivel</th>
                            <th>Grado</th>
                            <th>Sección</th>
                            <th>Capacidad</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sections as $s)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $s->schoolYear->nombre }}</td>
                            <td>{{ $s->grade->level->nombre }}</td>
                            <td>{{ $s->grade->nombre }}</td>
                            <td>{{ $s->nombre }}</td>
                            <td>{{ $s->capacidad ?? '-' }}</td>
                            <td>
                                <span class="badge {{ $s->activo ? 'badge-success' : 'badge-secondary' }}">
                                    {{ $s->activo ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('sections.edit', $s->id) }}"
                                   class="btn btn-warning btn-sm">
                                    <i class="fa fa-pen"></i>
                                </a>

                                <form action="{{ route('sections.destroy', $s->id) }}"
                                      method="POST" style="display:inline-block;">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-danger btn-sm"
                                        onclick="return confirm('¿Eliminar sección?')">
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
@endsection
