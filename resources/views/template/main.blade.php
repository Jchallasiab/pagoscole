<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') | IEP TESLA BLACK HORSE</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">

    <!-- Ionicons -->
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">

    <!-- Font Awesome 6 Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- DataTables -->
    <link rel="stylesheet" href="/assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="/assets/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
    <link rel="stylesheet" href="/assets/plugins/datatables-buttons/css/buttons.bootstrap4.min.css">

    <!-- Theme style -->
    <link rel="stylesheet" href="/assets/dist/css/adminlte.min.css">

    <!-- ✅ Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <!-- ✅ Ajuste visual para Select2 (para que se vea como los inputs normales) -->
    <style>
        .select2-container .select2-selection--single {
            height: 38px !important;
            padding: 6px 12px;
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
        }

        .select2-selection__rendered {
            line-height: 25px !important;
        }

        .select2-selection__arrow {
            height: 34px !important;
        }
    </style>
</head>

<body class="hold-transition sidebar-mini">

    @include('sweetalert::alert')

    <div class="wrapper">

        <!-- Navbar -->
        <nav class="main-header navbar navbar-expand navbar-white navbar-light">
            <!-- Left navbar links -->
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button">
                        <i class="fas fa-bars"></i>
                    </a>
                </li>
                <li class="nav-item d-none d-sm-inline-block">
                    <a href="/" class="nav-link">PANEL</a>
                </li>
            </ul>

            <!-- Right navbar links -->
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" data-widget="fullscreen" href="#" role="button">
                        <i class="fas fa-expand-arrows-alt"></i>
                    </a>
                </li>
            </ul>
        </nav>
        <!-- /.navbar -->

        <!-- Main Sidebar Container -->
        <aside class="main-sidebar sidebar-dark-primary elevation-4">
            <!-- Brand Logo -->
            <a href="/dashboard" class="brand-link">
                <img src="{{ asset('img/logotesla.jpg') }}" alt="Logo Tesla"
                    class="brand-image img-circle elevation-3" style="opacity: .8">
                <span class="brand-text font-weight-light">TESLA BLACK HORSE</span>
            </a>

            <!-- Sidebar -->
            <div class="sidebar">
                <!-- Sidebar user panel -->
                <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                    <div class="image">
                        <img src="{{ asset('img/logotesla.jpg') }}" class="img-circle elevation-2" alt="User Image">
                    </div>
                    <div class="info">
                        <a href="#" class="d-block">{{ auth()->user()->name }}</a>
                    </div>
                </div>

                <!-- Sidebar Menu -->
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                        data-accordion="false">
                        <li class="nav-item">
                            <a href="/" class="nav-link">
                                <i class="nav-icon fa-solid fa-gauge-high"></i>
                                <p>PANEL</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="/students" class="nav-link">
                                <i class="nav-icon fa-solid fa-user-graduate"></i>
                                <p>ESTUDIANTES</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="/enrollments" class="nav-link">
                                <i class="nav-icon fa-solid fa-file-signature"></i>
                                <p>MATRÍCULAS</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="/payments" class="nav-link">
                                <i class="nav-icon fa-solid fa-money-bill-wave"></i>
                                <p>PAGOS</p>
                            </a>
                        </li>

                        @if(auth()->user()->role === 'admin')
                            <li class="nav-item">
                                <a href="/users" class="nav-link">
                                    <i class="nav-icon fa-solid fa-users-cog"></i>
                                    <p>USUARIOS</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="/grades" class="nav-link">
                                    <i class="nav-icon fa-solid fa-layer-group"></i>
                                    <p>GRADOS</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="/sections" class="nav-link">
                                    <i class="nav-icon fa-solid fa-th-large"></i>
                                    <p>SECCIONES</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="/levels" class="nav-link">
                                    <i class="nav-icon fa-solid fa-school"></i>
                                    <p>NIVELES</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="/school_years" class="nav-link">
                                    <i class="nav-icon fa-solid fa-calendar-alt"></i>
                                    <p>AÑO ESCOLAR</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="/payment_concepts" class="nav-link">
                                    <i class="nav-icon fa-solid fa-money-check-dollar"></i>
                                    <p>CONCEPTOS DE PAGO</p>
                                </a>
                            </li>
                        @endif

                        <li class="nav-item">
                            <a class="log-out ml-3" href="#">
                                <i class="nav-icon fa-solid fa-power-off" style="color: red;"></i>
                                CERRAR SESIÓN
                                <form action="/logout" method="POST" id="logging-out">
                                    @csrf
                                </form>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </aside>

        <!-- CONTENIDO -->
        @yield('content')

        <!-- Main Footer -->
        <footer class="main-footer">
            <div class="float-right d-none d-sm-inline"></div>
            <strong>Copyright &copy; 2026
                <a href="/">JH</a>.
            </strong> Todos los derechos reservados.
        </footer>
    </div>
    <!-- ./wrapper -->

    <!-- REQUIRED SCRIPTS -->
    <script src="/assets/plugins/jquery/jquery.min.js"></script>
    <script src="/assets/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- DataTables -->
    <script src="/assets/plugins/datatables/jquery.dataTables.min.js"></script>
    <script src="/assets/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
    <script src="/assets/plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
    <script src="/assets/plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
    <script src="/assets/plugins/datatables-buttons/js/dataTables.buttons.min.js"></script>
    <script src="/assets/plugins/datatables-buttons/js/buttons.bootstrap4.min.js"></script>

    <!-- AdminLTE -->
    <script src="/assets/dist/js/adminlte.min.js"></script>

    <!-- SweetAlert -->
    @include('sweetalert::alert', ['cdn' => 'https://cdn.jsdelivr.net/npm/sweetalert2@9'])
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- ✅ Select2 -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- ✅ Scripts personalizados de las vistas -->
    @yield('scripts')

    <!-- ACTIVAR MENÚ ACTUAL -->
    <script>
        $(function() {
            var url = window.location;
            $('ul.nav-sidebar a').filter(function() {
                return this.href == url;
            }).addClass('active');
            $('ul.nav-treeview a').filter(function() {
                return this.href == url;
            }).parentsUntil(".nav-sidebar > .nav-treeview")
                .css({ 'display': 'block' })
                .addClass('menu-open').prev('a').addClass('active');
        });
    </script>

    <!-- Inicialización de DataTable -->
    <script>
        $(document).ready(function() {
            $('#example1').DataTable({
                responsive: true
            });
        });
    </script>

    <!-- Confirmar eliminación -->
    <script type="text/javascript">
        $(document).on('click', '#btn-delete', function(e) {
            e.preventDefault();
            var form = $(this).closest("form");
            Swal.fire({
                title: '¿Estás seguro?',
                text: "No podrás revertir esto",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#7367f0',
                cancelButtonColor: '#82868b',
                confirmButtonText: 'Sí, eliminar'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    </script>

    <!-- Validación de formularios -->
    <script>
        (function() {
            'use strict';
            window.addEventListener('load', function() {
                var forms = document.getElementsByClassName('needs-validation');
                Array.prototype.filter.call(forms, function(form) {
                    form.addEventListener('submit', function(event) {
                        if (form.checkValidity() === false) {
                            event.preventDefault();
                            event.stopPropagation();
                        }
                        form.classList.add('was-validated');
                    }, false);
                });
            }, false);
        })();
    </script>

    <!-- Logout con confirmación -->
    <script>
        $(".log-out").on('click', function(e) {
            e.preventDefault();
            Swal.fire({
                title: '¿Cerrar sesión?',
                text: "Tendrás que iniciar sesión nuevamente",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#7367f0',
                cancelButtonColor: '#82868b',
                confirmButtonText: 'Sí, cerrar sesión'
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#logging-out').submit();
                }
            });
        });
    </script>
</body>
</html>
