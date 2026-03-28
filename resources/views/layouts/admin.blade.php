<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - @yield('title', 'Dashboard') - Pescadería Don Walter</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/Administrador.css') }}">
    <link rel="stylesheet" href="{{ asset('css/reportes.css') }}">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-blue: #1A519D;
            --light-blue: #7CCCED;
        }
        .nav-link.active {
            background-color: var(--primary-blue) !important;
            color: white !important;
        }
        .sidebar .nav-link:hover {
            background-color: #f0f0f0;
            color: var(--primary-blue) !important;
        }
        /* Fix for dropdown menu appearing behind content */
        .nav-item.dropend {
            position: relative;
        }
        .dropdown-menu {
            z-index: 9999 !important;
        }
        /* Fix stack order: Navbar should be on top of Sidebar */
        .navbar.sticky-top {
            z-index: 2000 !important; /* Extremely high to stay on top */
        }
        /* Prevents sidebar from clipping the dropdown */
        #sidebarMenu {
            overflow: visible !important;
            z-index: 1000; /* Standard sidebar level */
        }
        /* Dashboard Card Enhancements */
        .stat-card {
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.15) !important;
        }
        .stat-card::after {
            content: "";
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, rgba(255,255,255,0.1) 0%, transparent 100%);
            transform: rotate(25deg);
            pointer-events: none;
        }
        /* ── Override: texto siempre oscuro en el panel (corrige herencia de Administrador.css) ── */
        body, .card, .card-body, .card-header,
        .form-label, .form-control, .form-select,
        .form-text, .table, h1, h2, h3, h4, h5, h6, p, span:not(.text-white):not(.navbar-brand span) {
            color: inherit;
        }
        body {
            color: #212529 !important; /* Negro Bootstrap estándar */
        }
        .form-label { color: #212529 !important; }
        .form-control, .form-select { color: #212529 !important; background-color: #fff !important; }
        .card-header h5, .card-header h6 { color: #212529 !important; }
        h6.text-primary { color: #1A519D !important; }
        label.text-secondary { color: #6c757d !important; }
        label.text-success  { color: #198754 !important; }

        /* ── Estandarización de Altura para Simetría Perfecta ── */
        .input-group .form-control, 
        .input-group .form-select,
        .input-group-text {
            height: 42px !important;
            display: flex;
            align-items: center;
        }
        .form-select {
            padding-top: 0 !important;
            padding-bottom: 0 !important;
            line-height: 42px !important;
        }
    </style>
    @stack('styles')
</head>
<body class="bg-light">

    @include('partials.admin_navbar')

    <!-- Alertas de Seguridad Globales -->
    @if(auth()->check())
        @if(auth()->user()->nombre_usuario === 'admin123')
            <div class="alert alert-danger rounded-0 border-0 m-0 text-center shadow-sm" style="z-index: 1050; position: relative;" role="alert">
                <i class="fas fa-exclamation-triangle fa-fade fa-lg me-2"></i>
                <strong>¡ALERTA CRÍTICA DE SEGURIDAD!</strong> Está utilizando las credenciales predeterminadas. Cualquiera que conozca el sistema podría vulnerar su tienda. <a href="{{ route('admin.configuracion.edit') }}#seguridad" class="alert-link text-decoration-underline">Cambie su usuario y contraseña inmediatamente aquí</a>.
            </div>
        @elseif(auth()->user()->updated_at && auth()->user()->updated_at->diffInDays(now()) >= 90)
            <div class="alert alert-warning rounded-0 border-0 m-0 text-center shadow-sm text-dark" style="z-index: 1050; position: relative;" role="alert">
                <i class="fas fa-history fa-lg me-2"></i>
                <strong>Aviso de Seguridad:</strong> Su contraseña actual tiene 90 días o más de antigüedad. Por políticas de seguridad, <a href="{{ route('admin.configuracion.edit') }}#seguridad" class="alert-link text-dark text-decoration-underline">le sugerimos actualizarla aquí</a>.
            </div>
        @endif
    @endif

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar Offcanvas para móviles y columna en PC -->
            <div class="offcanvas-md offcanvas-start bg-white shadow-sm vh-100 col-md-3 col-lg-2 p-0" tabindex="-1" id="sidebarMenu" aria-labelledby="sidebarMenuLabel">
                <div class="offcanvas-header border-bottom d-md-none">
                    <h5 class="offcanvas-title fw-bold" id="sidebarMenuLabel"><i class="fas fa-bars me-2 text-primary"></i> Menú</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#sidebarMenu" aria-label="Close"></button>
                </div>
                <div class="offcanvas-body d-md-flex flex-column p-0 pt-lg-3 overflow-y-auto">
                    <ul class="nav flex-column gap-2 px-3 w-100">
                        <li class="nav-item">
                            <a class="nav-link rounded {{ request()->routeIs('admin.dashboard') ? 'active' : 'text-dark' }}" href="{{ route('admin.dashboard') }}">
                                <i class="fas fa-home fa-fw me-2"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link rounded {{ request()->routeIs('admin.productos.*') ? 'active' : 'text-dark' }}" href="{{ route('admin.productos.index') }}">
                                <i class="fas fa-fish fa-fw me-2"></i> Productos
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link rounded {{ request()->routeIs('admin.categorias.*') ? 'active' : 'text-dark' }}" href="{{ route('admin.categorias.index') }}">
                                <i class="fas fa-tags fa-fw me-2"></i> Categorías
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link rounded {{ request()->routeIs('admin.pedidos.*') ? 'active' : 'text-dark' }}" href="{{ route('admin.pedidos.index') }}">
                                <i class="fas fa-box-open fa-fw me-2"></i> Pedidos
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link rounded {{ request()->routeIs('admin.clientes.*') ? 'active' : 'text-dark' }}" href="{{ route('admin.clientes.index') }}">
                                <i class="fas fa-users fa-fw me-2"></i> Clientes
                            </a>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link rounded text-dark dropdown-toggle d-flex align-items-center justify-content-between w-100 border-0 bg-transparent" data-bs-toggle="collapse" data-bs-target="#reportesMenu" aria-expanded="false" aria-controls="reportesMenu">
                                <span><i class="fas fa-chart-line fa-fw me-2"></i> Reportes</span>
                            </button>
                            <div class="collapse overflow-hidden" id="reportesMenu">
                                <ul class="nav flex-column ms-3 mt-1 pb-2">
                                    <li class="nav-item"><a class="nav-link rounded text-secondary py-1" href="{{ route('admin.reportes.ventas') }}"><i class="fas fa-angle-right me-2"></i>Ventas Totales</a></li>
                                    <li class="nav-item"><a class="nav-link rounded text-secondary py-1" href="{{ route('admin.reportes.mas-vendidos') }}"><i class="fas fa-angle-right me-2"></i>Más Vendidos</a></li>
                                    <li class="nav-item"><a class="nav-link rounded text-secondary py-1" href="{{ route('admin.reportes.bajo-stock') }}"><i class="fas fa-angle-right me-2"></i>Alerta Bajo Stock</a></li>
                                    <li class="nav-item"><a class="nav-link rounded text-secondary py-1" href="{{ route('admin.reportes.stock-categoria') }}"><i class="fas fa-angle-right me-2"></i>Stock Categoría</a></li>
                                </ul>
                            </div>
                        </li>
                        <li class="nav-item mt-4">
                            <a class="nav-link rounded {{ request()->routeIs('admin.configuracion.*') ? 'active' : 'text-secondary' }}" href="{{ route('admin.configuracion.edit') }}">
                                <i class="fas fa-cog fa-fw me-2"></i> Configuración
                            </a>
                        </li>
                        <li class="nav-item border-top mt-3 pt-3">
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="nav-link rounded text-danger bg-transparent border-0 w-100 text-start">
                                    <i class="fas fa-sign-out-alt fa-fw me-2"></i> Cerrar Sesión
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4 border-bottom">
                    <h1 class="h2">@yield('title', 'Dashboard')</h1>
                    @yield('actions')
                </div>

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.datatable').DataTable({
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
                }
            });
        });
    </script>
    @stack('scripts')
</body>
</html>
