@extends('template.main')
@section('title', 'Usuarios del Sistema')

@section('content')
<div class="content-wrapper">

    {{-- ENCABEZADO --}}
    <div class="content-header">
        <div class="container-fluid d-flex justify-content-between align-items-center">
            <h1 class="m-0"><strong>@yield('title')</strong></h1>

            <a href="{{ route('users.create') }}" class="btn btn-success">
                <i class="fa-solid fa-plus"></i> Nuevo Usuario
            </a>
        </div>
    </div>

    {{-- CONTENIDO --}}
    <div class="content">
        <div class="container-fluid">

            {{-- ALERTAS --}}
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show">
                    {{ $errors->first() }}
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
            @endif

            <div class="card shadow-sm">
                <div class="card-body">

                    @if($users->isEmpty())
                        <div class="text-center text-muted">
                            <i class="fa-solid fa-user-lock fa-2x mb-2"></i>
                            <p>No hay usuarios registrados.</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-hover text-center align-middle">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>Nombre</th>
                                        <th>Email</th>
                                        <th>Rol</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($users as $u)
                                    <tr>
                                        <td>{{ $loop->iteration + ($users->currentPage()-1)*$users->perPage() }}</td>
                                        <td>{{ $u->name }}</td>
                                        <td>{{ $u->email }}</td>
                                        <td>
                                            <span class="badge {{ $u->role == 'admin' ? 'badge-danger' : 'badge-info' }}">
                                                {{ ucfirst($u->role) }}
                                            </span>
                                        </td>
                                        <td>
                                            {{-- EDITAR --}}
                                            <a href="{{ route('users.edit', $u->id_user) }}"
                                               class="btn btn-warning btn-sm">
                                                <i class="fa-solid fa-pen"></i>
                                            </a>

                                            {{-- ELIMINAR --}}
                                            @if($u->role !== 'admin')
                                                <form action="{{ route('users.destroy', $u->id_user) }}"
                                                      method="POST"
                                                      style="display:inline-block;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn btn-danger btn-sm"
                                                        onclick="return confirm('¿Eliminar usuario {{ $u->name }}?')">
                                                        <i class="fa-solid fa-trash"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- PAGINACIÓN --}}
                        <div class="d-flex justify-content-end">
                            {{ $users->links('pagination::bootstrap-4') }}
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
