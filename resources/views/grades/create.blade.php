@extends('template.main')
@section('title', 'NUEVO GRADO')

@section('content')
<div class="content-wrapper">

    <div class="content-header text-center">
        <h1><strong>@yield('title')</strong></h1>
    </div>

    <div class="content">
        <div class="container-fluid">
            <div class="card shadow-sm">
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('grades.store') }}">
                        @csrf

                        <div class="row">
                            <!-- NIVEL -->
                            <div class="col-md-4">
                                <label><strong>Nivel</strong></label>
                                <select name="level_id" class="form-control" required>
                                    <option value="">Seleccione</option>
                                    @foreach($levels as $level)
                                        <option value="{{ $level->id }}">
                                            {{ $level->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- GRADO -->
                            <div class="col-md-4">
                                <label><strong>Grado</strong></label>
                                <input type="text"
                                       name="nombre"
                                       class="form-control"
                                       placeholder="Ej: 1° Primaria"
                                       required>
                            </div>

                            <!-- ACTIVO -->
                            <div class="col-md-4 d-flex align-items-center">
                                <div class="form-check mt-4">
                                    <input type="checkbox"
                                           name="activo"
                                           id="activo"
                                           class="form-check-input"
                                           checked>
                                    <label for="activo" class="form-check-label">
                                        <strong>Activo</strong>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- BOTONES -->
                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-success px-4">
                                <i class="fa fa-save"></i> Guardar
                            </button>

                            <a href="{{ route('grades.index') }}" class="btn btn-secondary px-4 ml-2">
                                Volver
                            </a>
                        </div>

                    </form>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
