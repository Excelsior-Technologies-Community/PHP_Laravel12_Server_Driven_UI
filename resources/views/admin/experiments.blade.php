<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Server-Driven UI - A/B Testing & Experimentation Studio</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background: #0f172a; color: #f8fafc; font-family: system-ui, -apple-system, sans-serif; }
        .exp-card {
            background: #1e293b;
            border: 1px solid #334155;
            border-radius: 14px;
            padding: 24px;
            margin-bottom: 24px;
            transition: all 0.25s ease;
        }
        .exp-card:hover {
            border-color: #38bdf8;
            box-shadow: 0 10px 30px rgba(56, 189, 248, 0.1);
        }
        .variant-box {
            background: #0b1120;
            border: 1px solid #334155;
            border-radius: 10px;
            padding: 16px;
        }
        .metric-badge {
            font-size: 1.3rem;
            font-weight: 700;
        }
    </style>
</head>
<body>
    @include('layouts.navigation')

    <div class="container py-3">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold text-light mb-1"><i class="bi bi-sliders text-warning"></i> A/B Testing & UI Experimentation Engine</h3>
                <p class="text-secondary mb-0">Evaluate server-driven variants dynamically without App Store or frontend deployments</p>
            </div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newExperimentModal">
                <i class="bi bi-plus-circle-fill me-1"></i> Launch New Experiment
            </button>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Active Experiments List -->
        <div class="row">
            @forelse($experiments as $exp)
                <div class="col-12">
                    <div class="exp-card">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge {{ $exp['status'] === 'active' ? 'bg-success' : 'bg-secondary' }} px-3 py-2">
                                    {{ strtoupper($exp['status']) }}
                                </span>
                                <h4 class="mb-0 text-light fw-bold">{{ $exp['name'] }}</h4>
                                <span class="badge bg-dark border border-secondary text-info">Screen: {{ strtoupper($exp['screen']) }}</span>
                                <span class="badge bg-primary">Traffic Split: {{ $exp['traffic_percent'] }}% / {{ 100 - $exp['traffic_percent'] }}%</span>
                            </div>

                            <div class="d-flex gap-2">
                                <form action="{{ route('admin.experiments.toggle', $exp['id']) }}" method="POST">
                                    @csrf
                                    <button class="btn btn-sm {{ $exp['status'] === 'active' ? 'btn-outline-warning' : 'btn-outline-success' }}">
                                        <i class="bi {{ $exp['status'] === 'active' ? 'bi-pause-circle' : 'bi-play-circle' }}"></i>
                                        {{ $exp['status'] === 'active' ? 'Pause Experiment' : 'Resume Experiment' }}
                                    </button>
                                </form>
                                <form action="{{ route('admin.experiments.destroy', $exp['id']) }}" method="POST" onsubmit="return confirm('Remove this experiment?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </div>
                        </div>

                        <!-- Target Component Info -->
                        @if($exp['target_component'])
                            <div class="small text-muted mb-3">
                                <i class="bi bi-bullseye text-danger"></i> Target Component: 
                                <strong class="text-light">{{ $exp['target_component']->name }}</strong> 
                                (Type: <code>{{ $exp['target_component']->type }}</code>)
                            </div>
                        @endif

                        <!-- Variant Comparison Grid -->
                        <div class="row g-3">
                            <!-- Control A -->
                            <div class="col-md-6">
                                <div class="variant-box border-secondary">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="badge bg-secondary">VARIANT A (CONTROL)</span>
                                        <span class="text-muted small">Standard Baseline</span>
                                    </div>

                                    <div class="row text-center my-3">
                                        <div class="col-4">
                                            <div class="text-muted small">IMPRESSIONS</div>
                                            <div class="metric-badge text-light">{{ number_format($exp['control']['impressions']) }}</div>
                                        </div>
                                        <div class="col-4">
                                            <div class="text-muted small">CLICKS</div>
                                            <div class="metric-badge text-info">{{ number_format($exp['control']['clicks']) }}</div>
                                        </div>
                                        <div class="col-4">
                                            <div class="text-muted small">CTR RATE</div>
                                            <div class="metric-badge text-primary">{{ $exp['control']['ctr'] }}%</div>
                                        </div>
                                    </div>

                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar bg-primary" style="width: {{ min(100, $exp['control']['ctr'] * 3) }}%"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Variant B -->
                            <div class="col-md-6">
                                <div class="variant-box border-warning">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="badge bg-warning text-dark"><i class="bi bi-lightning-charge-fill"></i> {{ strtoupper($exp['variant_name']) }}</span>
                                        @if($exp['lift'] > 0)
                                            <span class="badge bg-success"><i class="bi bi-arrow-up-right"></i> +{{ $exp['lift'] }}% CTR LIFT</span>
                                        @elseif($exp['lift'] < 0)
                                            <span class="badge bg-danger"><i class="bi bi-arrow-down-right"></i> {{ $exp['lift'] }}% CTR</span>
                                        @else
                                            <span class="badge bg-secondary">0.0% LIFT</span>
                                        @endif
                                    </div>

                                    <div class="row text-center my-3">
                                        <div class="col-4">
                                            <div class="text-muted small">IMPRESSIONS</div>
                                            <div class="metric-badge text-light">{{ number_format($exp['variant_b']['impressions']) }}</div>
                                        </div>
                                        <div class="col-4">
                                            <div class="text-muted small">CLICKS</div>
                                            <div class="metric-badge text-info">{{ number_format($exp['variant_b']['clicks']) }}</div>
                                        </div>
                                        <div class="col-4">
                                            <div class="text-muted small">CTR RATE</div>
                                            <div class="metric-badge {{ $exp['variant_b']['ctr'] >= $exp['control']['ctr'] ? 'text-success' : 'text-danger' }}">
                                                {{ $exp['variant_b']['ctr'] }}%
                                            </div>
                                        </div>
                                    </div>

                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar bg-warning" style="width: {{ min(100, $exp['variant_b']['ctr'] * 3) }}%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Variant Properties Preview -->
                        <div class="mt-3">
                            <details class="text-secondary small">
                                <summary class="cursor-pointer text-info"><i class="bi bi-code-square"></i> Inspect Variant B JSON Overrides</summary>
                                <pre class="bg-dark text-warning p-2 rounded mt-2 mb-0" style="font-size: 11px;">{{ json_encode($exp['variant_properties'], JSON_PRETTY_PRINT) }}</pre>
                            </details>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-5">
                    <i class="bi bi-sliders text-muted fs-1 d-block mb-3"></i>
                    <h5 class="text-light">No experiments configured yet</h5>
                    <p class="text-secondary">Create an A/B experiment to split traffic on any component dynamically.</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Modal: New Experiment -->
    <div class="modal fade" id="newExperimentModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content bg-dark text-light border-secondary">
                <form action="{{ route('admin.experiments.store') }}" method="POST">
                    @csrf
                    <div class="modal-header border-secondary">
                        <h5 class="modal-title"><i class="bi bi-sliders text-warning"></i> Launch New A/B Experiment</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-bold">EXPERIMENT NAME</label>
                            <input type="text" name="name" class="form-control bg-dark text-light border-secondary" placeholder="e.g. Black Friday Hero Banner CTA Test" required>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-bold">SCREEN</label>
                                <select name="screen" id="modalScreenSelect" class="form-select bg-dark text-light border-secondary" required>
                                    @foreach($screens as $scr)
                                        <option value="{{ $scr }}">{{ ucfirst($scr) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-bold">TARGET COMPONENT</label>
                                <select name="target_component_id" class="form-select bg-dark text-light border-secondary" required>
                                    @foreach($components as $c)
                                        <option value="{{ $c->id }}">#{{ $c->id }} - {{ $c->name }} ({{ $c->screen }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-bold">TRAFFIC ALLOCATION (% FOR VARIANT B)</label>
                                <div class="input-group">
                                    <input type="number" name="traffic_percent" class="form-control bg-dark text-light border-secondary" value="50" min="1" max="99" required>
                                    <span class="input-group-text bg-secondary text-light">%</span>
                                </div>
                                <span class="text-secondary small">Remaining traffic goes to Control Variant A</span>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-bold">VARIANT B NAME</label>
                                <input type="text" name="variant_name" class="form-control bg-dark text-light border-secondary" value="Variant B (Flash Deal)" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-muted small fw-bold">VARIANT B PROPERTIES (JSON OVERRIDES)</label>
                            <textarea name="variant_properties" rows="5" class="form-control bg-dark text-warning border-secondary font-monospace" required>{
  "title": "⚡ Special 50% Urgency Sale!",
  "content": "Only 5 hours left to claim half-off premium features.",
  "button_text": "Claim 50% Off ➔",
  "theme": "danger",
  "badge": "URGENT"
}</textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-secondary">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-rocket-takeoff"></i> Launch Experiment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
