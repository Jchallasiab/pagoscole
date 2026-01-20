@extends('template.main')
@section('title', 'NUEVO AÑO ESCOLAR')

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

                    <form method="POST" action="{{ route('school_years.store') }}">
                        @csrf

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label><strong>Año Escolar</strong></label>
                                    <input type="text" name="nombre" class="form-control"
                                           placeholder="Ejemplo: 2025" required>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label><strong>Fecha Inicio</strong></label>
                                    <input type="date" name="fecha_inicio" class="form-control">
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label><strong>Fecha Fin</strong></label>
                                    <input type="date" name="fecha_fin" class="form-control">
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input type="checkbox" name="activo" value="1"
                                           class="form-check-input" checked>
                                    <label class="form-check-label"><strong>Activo</strong></label>
                                </div>
                            </div>
                        </div>

                        <div class="text-center mt-4">
                            <button class="btn btn-success px-4">
                                <i class="fa fa-save"></i> Guardar
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
