<nav class="navbar navbar-expand-lg navbar-dark sticky-top shadow">
    <div class="container">
        <!-- Brand / Logo -->
        <a class="navbar-brand d-flex align-items-center" href="{{ route('home') }}">
            <img src="{{ asset('img/Pescaderia%20Don%20Walter%20logo.png') }}" alt="Logo Pescaderia Don Walter" class="me-2">
        </a>
        <button class="navbar-toggler position-absolute d-lg-none collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation" style="right: 15px; top: 15px;">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Search Bar -->
        <form class="d-flex mx-auto search-bar position-relative mt-3 mt-lg-0 w-100" style="max-width: 400px;" action="{{ route('home') }}" method="GET">
            <input class="form-control me-2" type="search" name="buscar" value="{{ request('buscar') }}" placeholder="Filtrar..." aria-label="Search">
            <button class="btn btn-search d-flex align-items-center justify-content-center" type="submit" aria-label="Buscar">
                <i class="fas fa-search"></i>
            </button>
        </form>

        <div class="collapse navbar-collapse mt-3 mt-lg-0" id="navbarNav">
            <!-- Menú central -->
            <ul class="navbar-nav mx-auto">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('home') ? 'active fw-bold' : '' }}" href="{{ route('home') }}">
                        <i class="fas fa-home"></i> Inicio
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('quienes.somos') ? 'active fw-bold' : '' }}" href="{{ route('quienes.somos') }}">
                        <i class="fas fa-info-circle"></i> Quiénes Somos
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('ayuda') ? 'active fw-bold' : '' }}" href="{{ route('ayuda') }}">
                        <i class="fas fa-circle-question"></i> Ayuda
                    </a>
                </li>
            </ul>

            <!-- Menú derecho interactivo (Auth/Carrito) -->
            <ul class="navbar-nav ms-auto align-items-center">
                @guest
                    <li class="nav-item">
                        <a href="{{ route('login') }}" class="btn btn-brand-soft px-3 me-2 fw-bold">
                            <i class="fas fa-sign-in-alt me-1"></i> Iniciar Sesión
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('register') }}" class="btn btn-brand-dark px-3 fw-bold">
                            <i class="fas fa-user-plus me-1"></i> Registrarse
                        </a>
                    </li>
                @else
                    @if(auth()->user()->esCliente())
                        <li class="nav-item me-3">
                            <a class="nav-link d-flex align-items-center" href="{{ route('carrito.index') }}">
                                <i class="fas fa-shopping-cart fa-lg"></i>
                                @php
                                    $cartCount = \App\Models\Carrito::where('usuario_id', auth()->id())->sum('cantidad');
                                @endphp
                                @if($cartCount > 0)
                                    <span class="badge badge-cart ms-1">{{ $cartCount }}</span>
                                @endif
                            </a>
                        </li>
                    @endif

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center fw-bold" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user-circle fa-lg me-2"></i> {{ auth()->user()->nombre_completo }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="navbarDropdown">
                            @if(auth()->user()->esAdmin())
                                <li><a class="dropdown-item" href="{{ route('admin.dashboard') }}"><i class="fas fa-cogs me-2 text-secondary"></i> Panel de Control</a></li>
                            @elseif(auth()->user()->esCliente())
                                <li><a class="dropdown-item" href="{{ route('pedidos.index') }}"><i class="fas fa-box-open me-2 text-secondary"></i> Mis Pedidos</a></li>
                            @endif
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="fas fa-sign-out-alt me-2"></i> Cerrar Sesión
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                @endguest
            </ul>
        </div>
    </div>
</nav>
