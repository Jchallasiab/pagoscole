@extends('template.main')
@section('title', 'NUEVA SECCIÓN')

@section('content')
<div class="content-wrapper">

    <div class="content-header text-center">
        <h1><strong>@yield('title')</strong></h1>
    </div>

    <div class="content">
        <div class="container-fluid">

            {{-- ALERT ERRORES --}}
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="card shadow-sm">
                <div class="card-body">

                    <form method="POST" action="{{ route('sections.store') }}">
                        @csrf

                        <div class="row">
                            {{-- AÑO ESCOLAR --}}
                            <div class="col-md-4">
                                <label><strong>Año Escolar</strong></label>
                                <select name="school_year_id"
                                        class="form-control"
                                        required>
                                    <option value="">Seleccione</option>
                                    @foreach($schoolYears as $y)
                                        <option value="{{ $y->id }}">
                                            {{ $y->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- GRADO --}}
                            <div class="col-md-4">
                                <label><strong>Grado</strong></label>
                                <select name="grade_id"
                                        class="form-control @error('grade_id') is-invalid @enderror"
                                        required>
                                    <option value="">Seleccione</option>
                                    @foreach($grades as $g)
                                        <option value="{{ $g->id }}" {{ old('grade_id') == $g->id ? 'selected' : '' }}>
                                            {{ $g->level->nombre }} - {{ $g->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- SECCIÓN --}}
                            <div class="col-md-4">
                                <label><strong>Sección</strong></label>
                                <input type="text"
                                       name="nombre"
                                       class="form-control @error('nombre') is-invalid @enderror"
                                       value="{{ old('nombre') }}"
                                       placeholder="Ej: A"
                                       required>
                                @error('nombre')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- CAPACIDAD --}}
                            <div class="col-md-2">
                                <label><strong>Capacidad</strong></label>
                                <input type="number"
                                       name="capacidad"
                                       class="form-control"
                                       value="{{ old('capacidad') }}">
                            </div>

                            {{-- ACTIVO --}}
                            <div class="col-md-2 d-flex align-items-center">
                                <div class="form-check mt-4">
                                    <input type="checkbox"
                                           name="activo"
                                           class="form-check-input"
                                           checked>
                                    <label class="form-check-label">
                                        <strong>Activo</strong>
                                    </label>
                                </div>
                            </div>

                        </div>

                        {{-- BOTONES --}}
                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-success px-4">
                                <i class="fa fa-save"></i> Guardar
                            </button>

                            <a href="{{ route('sections.index') }}" class="btn btn-secondary px-4 ml-2">
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
