@extends('template.main')
@section('title', 'EDITAR ESTUDIANTE')
@section('content')

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid text-center">
            <h1 class="m-0"><strong>@yield('title')</strong></h1>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            <div class="card shadow-sm">
                <div class="card-body">

                    <form action="{{ route('students.update', $student->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <!-- Primera fila -->
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label><strong>DNI:</strong></label>
                                    <input type="text" name="dni" maxlength="8" class="form-control" value="{{ old('dni', $student->dni) }}" required>
                                    @error('dni')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label><strong>NOMBRES:</strong></label>
                                    <input type="text" name="nombres" class="form-control" value="{{ old('nombres', $student->nombres) }}" required>
                                    @error('nombres')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label><strong>APELLIDO PATERNO:</strong></label>
                                    <input type="text" name="apellido_paterno" class="form-control" value="{{ old('apellido_paterno', $student->apellido_paterno) }}" required>
                                    @error('apellido_paterno')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                            </div>
                        </div>

                        <!-- Segunda fila -->
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label><strong>APELLIDO MATERNO:</strong></label>
                                    <input type="text" name="apellido_materno" class="form-control" value="{{ old('apellido_materno', $student->apellido_materno) }}" required>
                                    @error('apellido_materno')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label><strong>CORREO ELECTRÓNICO:</strong></label>
                                    <input type="email" name="email" class="form-control" value="{{ old('email', $student->email) }}">
                                    @error('email')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label><strong>CELULAR:</strong></label>
                                    <input type="text" name="celular" maxlength="9" class="form-control" value="{{ old('celular', $student->celular) }}" required>
                                    @error('celular')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                            </div>
                        </div>

                        <!-- Tercera fila -->
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label><strong>DIRECCIÓN:</strong></label>
                                    <input type="text" name="direccion" class="form-control" value="{{ old('direccion', $student->direccion) }}" required>
                                    @error('direccion')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label><strong>NOMBRE DEL APODERADO:</strong></label>
                                    <input type="text" name="nombre_apoderado" class="form-control" value="{{ old('nombre_apoderado', $student->nombre_apoderado) }}">
                                    @error('nombre_apoderado')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label><strong>CELULAR DEL APODERADO:</strong></label>
                                    <input type="text" name="celular_apoderado" maxlength="9" class="form-control" value="{{ old('celular_apoderado', $student->celular_apoderado) }}">
                                    @error('celular_apoderado')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                            </div>
                        </div>

                        <!-- Cuarta fila: foto y estado -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label fw-bold">FOTO DEL ESTUDIANTE:</label>
                                    <input 
                                        type="file" 
                                        name="photo" 
                                        class="form-control"
                                        accept="image/*">

                                    <small class="form-text text-muted">
                                        Dejar en blanco para mantener la foto actual.
                                    </small>

                                    @error('photo')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror

                                    @if($student->photo_path)
                                        <small class="text-success d-block mt-1">
                                            ✔ El estudiante ya tiene una foto registrada.
                                        </small>
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><strong>ESTADO:</strong></label>
                                    <select name="estado" class="form-control">
                                        <option value="activo" {{ old('estado', $student->estado) == 'activo' ? 'selected' : '' }}>Activo</option>
                                        <option value="inactivo" {{ old('estado', $student->estado) == 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                                        <option value="retirado" {{ old('estado', $student->estado) == 'retirado' ? 'selected' : '' }}>Retirado</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Botones -->
                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="fa-solid fa-floppy-disk"></i> ACTUALIZAR
                            </button>
                            <a href="{{ route('students.index') }}" class="btn btn-secondary px-4">
                                <i class="fa-solid fa-arrow-left"></i> VOLVER
                            </a>
                        </div>

                    </form>

                </div>
            </div>
        </div>
    </div>
</div>

@endsection
