@extends('template.main')
@section('title', 'EDITAR AÑO ESCOLAR')

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

                    <form method="POST"
                          action="{{ route('school_years.update', $school_year->id) }}">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label><strong>Año Escolar</strong></label>
                                    <input type="text" name="nombre"
                                           value="{{ $school_year->nombre }}"
                                           class="form-control" required>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label><strong>Fecha Inicio</strong></label>
                                    <input type="date" name="fecha_inicio"
                                           value="{{ $school_year->fecha_inicio }}"
                                           class="form-control">
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label><strong>Fecha Fin</strong></label>
                                    <input type="date" name="fecha_fin"
                                           value="{{ $school_year->fecha_fin }}"
                                           class="form-control">
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input type="checkbox" name="activo" value="1"
                                           class="form-check-input"
                                           {{ $school_year->activo ? 'checked' : '' }}>
                                    <label class="form-check-label"><strong>Activo</strong></label>
                                </div>
                            </div>
                        </div>

                        <div class="text-center mt-4">
                            <button class="btn btn-primary px-4">
                                <i class="fa fa-save"></i> Actualizar
                            </button>
                            <a href="{{ route('school_years.index') }}"
                               class="btn btn-secondary px-4 ml-2">
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
