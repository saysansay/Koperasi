<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('app.app_name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="sap-theme">
<div class="container-fluid">
    <div class="row app-shell">
        <aside id="appSidebar" class="col-lg-2 col-md-3 sidebar p-3 text-white">
            <div class="mb-4 pb-2 border-bottom border-light border-opacity-25">
                <h4 class="fw-bold mb-1">{{ __('app.app_name') }}</h4>
                <div class="small text-white-50">{{ __('app.company_name') }}</div>
            </div>
            <nav class="nav flex-column">
                @foreach(auth()->user()->visibleMenus() as $menu)
                    @php
                        $routeName = $menu['route'];
                        $activePattern = str_contains($routeName, '.') ? explode('.', $routeName)[0].'.*' : $routeName;
                    @endphp
                    <a class="nav-link {{ request()->routeIs($activePattern) ? 'active' : '' }}" href="{{ route($routeName) }}">
                        <i class="bi {{ $menu['icon'] }} me-2"></i>{{ __('app.'.$menu['label']) }}
                    </a>
                @endforeach
            </nav>
        </aside>
        <main id="appMain" class="col-lg-10 col-md-9 ms-sm-auto px-md-4 py-3">
            <div class="topbar p-3 mb-4 d-flex flex-wrap justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-light btn-sm sidebar-toggle" id="sidebarToggle" aria-label="Toggle sidebar" aria-expanded="true">
                        <i class="bi bi-layout-sidebar-inset"></i>
                    </button>
                    <div>
                    <h3 class="mb-0 fw-bold">@yield('title', __('app.dashboard'))</h3>
                    <div class="text-muted small">{{ now()->format('l, d M Y') }}</div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <div class="dropdown">
                        <button class="btn btn-light btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            {{ strtoupper(app()->getLocale()) }}
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li class="dropdown-header">{{ __('app.language') }}</li>
                            <li>
                                <form method="POST" action="{{ route('locale.update') }}">
                                    @csrf
                                    <input type="hidden" name="locale" value="en">
                                    <button class="dropdown-item" @disabled(app()->getLocale() === 'en')>{{ __('app.english') }}</button>
                                </form>
                            </li>
                            <li>
                                <form method="POST" action="{{ route('locale.update') }}">
                                    @csrf
                                    <input type="hidden" name="locale" value="id">
                                    <button class="dropdown-item" @disabled(app()->getLocale() === 'id')>{{ __('app.indonesian') }}</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-light btn-sm dropdown-toggle text-start user-menu-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="fw-semibold">{{ auth()->user()->name }}</div>
                            <div class="small text-muted text-uppercase">{{ auth()->user()->role?->name }}</div>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="{{ route('profile.show') }}"><i class="bi bi-person me-2"></i>{{ __('app.profile') }}</a></li>
                            <li><a class="dropdown-item" href="{{ route('profile.password.edit') }}"><i class="bi bi-key me-2"></i>{{ __('app.change_password') }}</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button class="dropdown-item text-danger"><i class="bi bi-box-arrow-right me-2"></i>{{ __('app.logout') }}</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    (() => {
        const body = document.body;
        const toggle = document.getElementById('sidebarToggle');
        const storageKey = 'koperasi-sidebar-collapsed';

        if (localStorage.getItem(storageKey) === 'true') {
            body.classList.add('sidebar-collapsed');
            toggle?.setAttribute('aria-expanded', 'false');
        }

        toggle?.addEventListener('click', () => {
            const collapsed = body.classList.toggle('sidebar-collapsed');
            localStorage.setItem(storageKey, collapsed ? 'true' : 'false');
            toggle.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
        });
    })();
</script>
@stack('scripts')
</body>
</html>
