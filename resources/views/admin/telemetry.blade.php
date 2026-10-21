<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Server-Driven UI - Analytics Radar & CTR Heatmap</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background: #0f172a; color: #f8fafc; font-family: system-ui, -apple-system, sans-serif; }
        .kpi-card {
            background: #1e293b;
            border: 1px solid #334155;
            border-radius: 14px;
            padding: 20px;
            transition: all 0.25s ease;
        }
        .kpi-card:hover {
            border-color: #38bdf8;
            box-shadow: 0 10px 25px rgba(56, 189, 248, 0.1);
        }
        .heat-high {
            background: rgba(34, 197, 94, 0.15);
            border-left: 4px solid #22c55e;
        }
        .heat-medium {
            background: rgba(234, 179, 8, 0.15);
            border-left: 4px solid #eab308;
        }
        .heat-low {
            background: rgba(239, 68, 68, 0.15);
            border-left: 4px solid #ef4444;
        }
        .heatmap-table th {
            background: #1e293b;
            color: #94a3b8;
            border-color: #334155;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .heatmap-table td {
            background: #0f172a;
            color: #f1f5f9;
            border-color: #334155;
            vertical-align: middle;
        }
    </style>
</head>
<body>
    @include('layouts.navigation')

    <div class="container py-3">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold text-light mb-1"><i class="bi bi-activity text-info"></i> SDUI Analytics Radar & Component CTR Heatmap</h3>
                <p class="text-secondary mb-0">Telemetry observability tracking impressions, user interactions, and component engagement</p>
            </div>
            <div class="d-flex gap-2">
                <form action="{{ route('admin.telemetry.synthetic') }}" method="POST">
                    @csrf
                    <button class="btn btn-outline-info">
                        <i class="bi bi-cpu me-1"></i> Simulate 150+ Live Telemetry Events
                    </button>
                </form>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- KPI Summary Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="kpi-card">
                    <span class="text-secondary small fw-bold">TOTAL SERVED IMPRESSIONS</span>
                    <div class="fs-2 fw-bold text-light mt-1">{{ number_format($totalImpressions) }}</div>
                    <span class="text-muted small"><i class="bi bi-eye"></i> Server-driven renders</span>
                </div>
            </div>

            <div class="col-md-3">
                <div class="kpi-card">
                    <span class="text-secondary small fw-bold">USER INTERACTIONS / CLICKS</span>
                    <div class="fs-2 fw-bold text-info mt-1">{{ number_format($totalClicks) }}</div>
                    <span class="text-muted small"><i class="bi bi-cursor-fill"></i> Validated user actions</span>
                </div>
            </div>

            <div class="col-md-3">
                <div class="kpi-card">
                    <span class="text-secondary small fw-bold">GLOBAL CLICK-THROUGH RATE</span>
                    <div class="fs-2 fw-bold {{ $overallCtr >= 15 ? 'text-success' : ($overallCtr >= 5 ? 'text-warning' : 'text-danger') }} mt-1">
                        {{ $overallCtr }}%
                    </div>
                    <span class="text-muted small"><i class="bi bi-pie-chart"></i> Conversion ratio</span>
                </div>
            </div>

            <div class="col-md-3">
                <div class="kpi-card">
                    <span class="text-secondary small fw-bold">CLIENT PLATFORM RADAR</span>
                    <div class="d-flex justify-content-between mt-2 small">
                        <span><i class="bi bi-laptop"></i> Web: <strong>{{ $platformStats['web'] ?? 0 }}</strong></span>
                        <span><i class="bi bi-apple"></i> iOS: <strong>{{ $platformStats['ios'] ?? 0 }}</strong></span>
                        <span><i class="bi bi-android2"></i> Android: <strong>{{ $platformStats['android'] ?? 0 }}</strong></span>
                    </div>
                    <div class="progress mt-2" style="height: 6px;">
                        @php
                            $sum = array_sum($platformStats) ?: 1;
                            $webPct = round((($platformStats['web'] ?? 0) / $sum) * 100);
                            $iosPct = round((($platformStats['ios'] ?? 0) / $sum) * 100);
                            $androidPct = round((($platformStats['android'] ?? 0) / $sum) * 100);
                        @endphp
                        <div class="progress-bar bg-info" style="width: {{ $webPct }}%"></div>
                        <div class="progress-bar bg-primary" style="width: {{ $iosPct }}%"></div>
                        <div class="progress-bar bg-success" style="width: {{ $androidPct }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter bar -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="d-flex align-items-center gap-2">
                <span class="text-muted small">Filter by Screen:</span>
                <div class="btn-group btn-group-sm">
                    <a href="{{ route('admin.telemetry', ['screen' => 'all']) }}" class="btn {{ $selectedScreen === 'all' ? 'btn-primary' : 'btn-outline-secondary text-light' }}">All Screens</a>
                    @foreach($screens as $scr)
                        <a href="{{ route('admin.telemetry', ['screen' => $scr]) }}" class="btn {{ $selectedScreen === $scr ? 'btn-primary' : 'btn-outline-secondary text-light' }}">{{ ucfirst($scr) }}</a>
                    @endforeach
                </div>
            </div>

            <div class="d-flex gap-2">
                <span class="badge bg-success"><i class="bi bi-circle-fill"></i> High CTR (≥15%)</span>
                <span class="badge bg-warning text-dark"><i class="bi bi-circle-fill"></i> Moderate (5-15%)</span>
                <span class="badge bg-danger"><i class="bi bi-circle-fill"></i> Low / Zero (&lt;5%)</span>
            </div>
        </div>

        <!-- Heatmap Table -->
        <div class="card bg-dark border-secondary shadow-sm">
            <div class="card-header border-secondary d-flex justify-content-between align-items-center py-3">
                <h6 class="mb-0 text-light fw-bold"><i class="bi bi-fire text-danger"></i> Component Engagement Heatmap & CTR Rank</h6>
                <span class="badge bg-secondary">{{ count($components) }} Components Indexed</span>
            </div>
            <div class="table-responsive">
                <table class="table heatmap-table mb-0">
                    <thead>
                        <tr>
                            <th>Component</th>
                            <th>Type</th>
                            <th>Screen</th>
                            <th>Platforms</th>
                            <th class="text-center">Impressions</th>
                            <th class="text-center">Clicks</th>
                            <th>Engagement CTR Bar</th>
                            <th class="text-center">CTR %</th>
                            <th class="text-center">Heat Indicator</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($components as $comp)
                            <tr class="heat-{{ $comp['heat'] }}">
                                <td>
                                    <div class="fw-bold text-light">#{{ $comp['id'] }} {{ $comp['name'] }}</div>
                                </td>
                                <td>
                                    <span class="badge bg-dark border border-secondary text-secondary">{{ strtoupper($comp['type']) }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">{{ ucfirst($comp['screen']) }}</span>
                                </td>
                                <td>
                                    @foreach($comp['platforms'] as $p)
                                        <span class="badge bg-dark text-info border border-info" style="font-size: 10px;">{{ strtoupper($p) }}</span>
                                    @endforeach
                                </td>
                                <td class="text-center fw-semibold">{{ number_format($comp['impressions']) }}</td>
                                <td class="text-center fw-semibold text-info">{{ number_format($comp['clicks']) }}</td>
                                <td style="width: 180px;">
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar bg-{{ $comp['heatBadge'] }}" style="width: {{ min(100, $comp['ctr'] * 3.3) }}%"></div>
                                    </div>
                                </td>
                                <td class="text-center fw-bold text-{{ $comp['heatBadge'] }}">{{ $comp['ctr'] }}%</td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $comp['heatBadge'] }} px-2 py-1">
                                        {{ $comp['heatLabel'] }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4 text-muted">No telemetry events logged yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
