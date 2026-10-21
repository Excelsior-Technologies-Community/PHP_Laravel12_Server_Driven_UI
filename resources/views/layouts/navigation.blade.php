<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm mb-4">
    <div class="container-fluid px-4">
        <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="{{ route('admin') }}">
            <span class="badge bg-primary px-2 py-1">SDUI</span>
            <span>Server-Driven UI Studio</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#sduiNavbar">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="sduiNavbar">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 gap-1">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('demo') ? 'active text-primary fw-semibold' : '' }}" href="{{ route('demo') }}">
                        <i class="bi bi-phone"></i> Live Client Demo
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin') ? 'active text-primary fw-semibold' : '' }}" href="{{ route('admin') }}">
                        <i class="bi bi-grid-3x3-gap"></i> Components Console
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.studio') ? 'active text-primary fw-semibold' : '' }}" href="{{ route('admin.studio') }}">
                        <i class="bi bi-palette"></i> Canvas Studio
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.experiments*') ? 'active text-primary fw-semibold' : '' }}" href="{{ route('admin.experiments') }}">
                        <i class="bi bi-sliders"></i> A/B Experiments
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.telemetry*') ? 'active text-primary fw-semibold' : '' }}" href="{{ route('admin.telemetry') }}">
                        <i class="bi bi-activity"></i> CTR Telemetry Radar
                    </a>
                </li>
            </ul>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-secondary">Laravel 12.49</span>
                <span class="badge bg-success"><i class="bi bi-check-circle-fill"></i> SDUI Engine Active</span>
            </div>
        </div>
    </div>
</nav>
