@extends('template.main')
@section('title', 'DETALLE DEL ESTUDIANTE')
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
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fa-solid fa-user-graduate"></i> {{ $student->nombres }} {{ $student->apellido_paterno }} {{ $student->apellido_materno }}
                    </h5>
                </div>

                <div class="card-body">
                    <div class="row">
                        <!-- Foto -->
                        <div class="col-md-4 text-center mb-3">
                            @if($student->photo_path)
                                <img src="{{ route('mostrar.foto', basename($student->photo_path)) }}" 
                                    alt="Foto del estudiante"
                                    class="img-fluid rounded shadow-sm"
                                    style="max-width: 250px;">
                            @else
                                <img src="{{ asset('img/default-avatar.png') }}" 
                                    alt="Sin foto"
                                    class="img-fluid rounded-circle opacity-75" 
                                    style="max-width: 200px;">
                            @endif

                            <div class="mt-3">
                                <span class="badge
                                    @if($student->estado == 'activo') badge-success
                                    @elseif($student->estado == 'inactivo') badge-secondary
                                    @else badge-danger
                                    @endif">
                                    {{ ucfirst($student->estado) }}
                                </span>
                            </div>
                        </div>

                        <!-- Datos -->
                        <div class="col-md-8">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <tbody>
                                        <tr>
                                            <th width="35%">DNI</th>
                                            <td>{{ $student->dni }}</td>
                                        </tr>
                                        <tr>
                                            <th>Nombres</th>
                                            <td>{{ $student->nombres }}</td>
                                        </tr>
                                        <tr>
                                            <th>Apellidos</th>
                                            <td>{{ $student->apellido_paterno }} {{ $student->apellido_materno }}</td>
                                        </tr>
                                        <tr>
                                            <th>Correo electrónico</th>
                                            <td>{{ $student->email ?? '—' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Celular</th>
                                            <td>{{ $student->celular }}</td>
                                        </tr>
                                        <tr>
                                            <th>Dirección</th>
                                            <td>{{ $student->direccion }}</td>
                                        </tr>
                                        <tr>
                                            <th>Nombre del apoderado</th>
                                            <td>{{ $student->nombre_apoderado ?? '—' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Celular del apoderado</th>
                                            <td>{{ $student->celular_apoderado ?? '—' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Registrado el</th>
                                            <td>{{ $student->created_at->format('d/m/Y H:i') }}</td>
                                        </tr>
                                        <tr>
                                            <th>Última actualización</th>
                                            <td>{{ $student->updated_at->format('d/m/Y H:i') }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Botones -->
                    <div class="text-center mt-4">
                        <a href="{{ route('students.edit', $student->id) }}" class="btn btn-warning px-4">
                            <i class="fa-solid fa-pen"></i> Editar
                        </a>
                        <a href="{{ route('students.index') }}" class="btn btn-secondary px-4">
                            <i class="fa-solid fa-arrow-left"></i> Volver
                        </a>

                        @if(auth()->user()->role === 'admin')
                            <form action="{{ route('students.destroy', $student->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger px-4"
                                    onclick="return confirm('¿Seguro que deseas eliminar este estudiante?')">
                                    <i class="fa-solid fa-trash"></i> Eliminar
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
