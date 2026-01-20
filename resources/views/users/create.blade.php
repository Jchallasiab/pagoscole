@extends('template.main')
@section('title', 'Registrar Usuario')

@section('content')
<div class="content-wrapper">

    {{-- ENCABEZADO --}}
    <div class="content-header text-center">
        <div class="container-fluid">
            <h1 class="m-0 font-weight-bold">@yield('title')</h1>
        </div>
    </div>

    {{-- FORMULARIO --}}
    <div class="content">
        <div class="container-fluid">
            <div class="card shadow-sm">
                <div class="card-body">

                    {{-- ERRORES --}}
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $e)
                                    <li>{{ $e }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('users.store') }}" method="POST">
                        @csrf

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Nombre</label>
                                <input type="text"
                                       name="name"
                                       value="{{ old('name') }}"
                                       class="form-control"
                                       required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>Email</label>
                                <input type="email"
                                       name="email"
                                       value="{{ old('email') }}"
                                       class="form-control"
                                       required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Contraseña</label>
                                <input type="password"
                                       name="password"
                                       class="form-control"
                                       required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>Confirmar Contraseña</label>
                                <input type="password"
                                       name="password_confirmation"
                                       class="form-control"
                                       required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Rol del Usuario</label>
                            <select name="role" class="form-control" required>
                                <option value="">Seleccione un rol</option>
                                <option value="secretaria">Secretaria</option>
                                <option value="admin">Administrador</option>
                            </select>
                        </div>

                        <div class="text-center mt-4">
                            <button class="btn btn-success px-4">
                                <i class="fa-solid fa-save"></i> Guardar Usuario
                            </button>
                            <a href="{{ route('users.index') }}" class="btn btn-secondary px-4">
                                Cancelar
                            </a>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
