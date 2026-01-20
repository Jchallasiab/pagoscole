@extends('template.main')
@section('title', 'PANEL DE CONTROL')
@section('content')

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <h1 class="m-0"><strong>@yield('title')</strong></h1>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">

            <!-- Summary Boxes -->
            <div class="row">
                <div class="col-lg-4 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3>{{ $totalStudents }}</h3>
                            <p>ESTUDIANTES</p>
                        </div>
                        <div class="icon"><i class="fas fa-user-graduate"></i></div>
                    </div>
                </div>

                <div class="col-lg-4 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3>{{ $totalEnrollments }}</h3>
                            <p>MATRICULADOS</p>
                        </div>
                        <div class="icon"><i class="fas fa-file-signature"></i></div>
                    </div>
                </div>

                <div class="col-lg-4 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3>{{ $pendingPayments }}</h3>
                            <p>PAGOS PENDIENTES</p>
                        </div>
                        <div class="icon"><i class="fas fa-money-bill-wave"></i></div>
                    </div>
                </div>
            </div>

            <!-- Levels & Grades -->
            <div class="row">
                @foreach($levels as $level)
                    <div class="col-md-6">
                        <div class="card shadow-sm mb-3">
                            <div class="card-header bg-primary text-white">
                                Nivel: {{ $level->nombre }}
                            </div>
                            <div class="card-body">
                                <ul class="list-group">
                                    @foreach($level->grades as $grade)
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            {{ $grade->nombre }}
                                            <span class="badge badge-info badge-pill">{{ $grade->enrollments_count }} matriculados</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

        </div>
    </div>
</div>

@endsection
