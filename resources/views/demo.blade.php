<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Server-Driven UI Demo & Multi-Platform Simulator</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background: #0f172a; color: #f8fafc; font-family: system-ui, -apple-system, sans-serif; }
        .control-panel {
            background: #1e293b;
            border: 1px solid #334155;
            border-radius: 14px;
            padding: 18px 24px;
            margin-bottom: 24px;
        }
        .component-card {
            background: #1e293b;
            border: 1px solid #334155;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 16px;
            transition: all 0.25s ease;
            position: relative;
        }
        .component-card:hover {
            border-color: #38bdf8;
            box-shadow: 0 8px 24px rgba(56, 189, 248, 0.12);
        }
        .device-wrapper {
            background: #0f172a;
            border-radius: 20px;
            display: flex;
            justify-content: center;
            padding: 20px 0;
        }
        .device-iphone {
            width: 410px;
            min-height: 720px;
            border: 12px solid #334155;
            border-radius: 44px;
            background: #0b1120;
            padding: 16px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.6);
            position: relative;
        }
        .dynamic-island {
            width: 120px;
            height: 26px;
            background: #000;
            border-radius: 20px;
            margin: 0 auto 16px auto;
        }
        .device-pixel {
            width: 410px;
            min-height: 720px;
            border: 10px solid #1e293b;
            border-radius: 36px;
            background: #0b1120;
            padding: 16px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.6);
            position: relative;
        }
        .camera-punchhole {
            width: 14px;
            height: 14px;
            background: #000;
            border-radius: 50%;
            margin: 0 auto 16px auto;
        }
        .device-desktop {
            width: 100%;
            background: #0b1120;
            border: 1px solid #334155;
            border-radius: 16px;
            padding: 24px;
            min-height: 600px;
        }
        .toast-bubble {
            position: fixed;
            bottom: 24px;
            right: 24px;
            z-index: 9999;
        }
    </style>
</head>
<body>
    @include('layouts.navigation')

    <div class="container py-2">
        <!-- Control Bar: Platform, App Version, Visitor ID, Screen -->
        <div class="control-panel shadow-sm">
            <div class="row g-3 align-items-center">
                <div class="col-lg-3">
                    <label class="form-label text-muted small mb-1 fw-bold">ACTIVE SCREEN</label>
                    <div class="btn-group w-100" role="group">
                        @foreach(['home' => 'Home', 'dashboard' => 'Dashboard', 'profile' => 'Profile', 'settings' => 'Settings'] as $scrKey => $scrLabel)
                            <a href="{{ route('demo', ['screen' => $scrKey, 'platform' => $platform, 'app_version' => $version, 'visitor_id' => $visitorId]) }}" 
                               class="btn btn-sm {{ $screen == $scrKey ? 'btn-primary' : 'btn-outline-secondary text-light' }}">
                                {{ $scrLabel }}
                            </a>
                        @endforeach
                    </div>
                </div>

                <div class="col-lg-3">
                    <label class="form-label text-muted small mb-1 fw-bold">DEVICE & CLIENT PLATFORM</label>
                    <select id="platformSelect" class="form-select form-select-sm bg-dark text-light border-secondary">
                        <option value="web" {{ $platform == 'web' ? 'selected' : '' }}>💻 Desktop Web (all/web)</option>
                        <option value="ios" {{ $platform == 'ios' ? 'selected' : '' }}>📱 Apple iOS (all/ios)</option>
                        <option value="android" {{ $platform == 'android' ? 'selected' : '' }}>🤖 Google Android (all/android)</option>
                    </select>
                </div>

                <div class="col-lg-2">
                    <label class="form-label text-muted small mb-1 fw-bold">APP VERSION GATING</label>
                    <input type="text" id="versionInput" class="form-control form-control-sm bg-dark text-light border-secondary" 
                           value="{{ $version }}" placeholder="e.g. 1.0.0 or 2.5.0">
                </div>

                <div class="col-lg-2">
                    <label class="form-label text-muted small mb-1 fw-bold">VISITOR ID (A/B BUCKET)</label>
                    <div class="input-group input-group-sm">
                        <input type="text" id="visitorInput" class="form-control bg-dark text-light border-secondary" value="{{ $visitorId }}">
                        <button class="btn btn-outline-info" id="btnRandomVisitor" title="Randomize Visitor ID to test A/B sticky bucket"><i class="bi bi-shuffle"></i></button>
                    </div>
                </div>

                <div class="col-lg-2 text-end">
                    <label class="form-label text-muted small mb-1 d-block">&nbsp;</label>
                    <button class="btn btn-sm btn-success w-100" id="btnApplyFilters">
                        <i class="bi bi-arrow-repeat"></i> Reload Schema
                    </button>
                </div>
            </div>
        </div>

        <!-- Meta status banner -->
        <div id="meta-banner" class="alert alert-dark border-secondary d-flex justify-content-between align-items-center py-2 px-3 small">
            <div>
                <span class="text-secondary">Client Handshake:</span>
                <span class="badge bg-primary ms-1" id="badge-platform">Platform: {{ strtoupper($platform) }}</span>
                <span class="badge bg-secondary ms-1" id="badge-version">v{{ $version }}</span>
                <span class="badge bg-dark border border-secondary text-light ms-1" id="badge-visitor">Visitor: {{ $visitorId }}</span>
            </div>
            <div id="badge-experiment-status">
                <span class="text-muted">A/B Engine: Calculating bucket...</span>
            </div>
        </div>

        <!-- Viewport & Raw Inspector -->
        <div class="row">
            <!-- Simulated Frame -->
            <div class="col-lg-7">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h5 class="mb-0 text-info"><i class="bi bi-display"></i> Dynamic Server-Driven Viewport</h5>
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-outline-secondary text-light active" onclick="switchFrame('desktop')">💻 Desktop</button>
                        <button type="button" class="btn btn-outline-secondary text-light" onclick="switchFrame('iphone')">📱 iPhone</button>
                        <button type="button" class="btn btn-outline-secondary text-light" onclick="switchFrame('pixel')">🤖 Pixel</button>
                    </div>
                </div>

                <div class="device-wrapper">
                    <div id="deviceFrame" class="device-desktop">
                        <div id="islandHeader" style="display:none;" class="dynamic-island"></div>
                        <div id="pixelHeader" style="display:none;" class="camera-punchhole"></div>

                        <div id="ui-container">
                            <div class="text-center py-5">
                                <div class="spinner-border text-primary" role="status"></div>
                                <p class="text-muted mt-2">Streaming Server-Driven Schema...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Raw Schema Inspector -->
            <div class="col-lg-5">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h5 class="mb-0 text-light"><i class="bi bi-code-slash"></i> Server Payload (JSON Schema)</h5>
                    <button class="btn btn-sm btn-outline-primary" onclick="copyRawJson()"><i class="bi bi-clipboard"></i> Copy JSON</button>
                </div>
                <div class="card bg-dark border-secondary">
                    <div class="card-header border-secondary d-flex justify-content-between py-2 small text-muted">
                        <span>Endpoint: <code>/api/ui/components/{{ $screen }}</code></span>
                        <span class="text-success"><i class="bi bi-broadcast"></i> HTTP 200 OK</span>
                    </div>
                    <div class="card-body p-2">
                        <pre id="api-response" class="bg-black text-info p-3 rounded small mb-0" style="max-height: 650px; overflow-y: auto; font-family: monospace;"></pre>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Live Toast Notification for Telemetry Click -->
    <div id="toastNotification" class="toast-bubble alert alert-success border-success shadow" style="display:none;">
        <i class="bi bi-check-circle-fill me-1"></i> <span id="toastMsg">Telemetry interaction recorded!</span>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let currentPayload = null;

        function switchFrame(type) {
            const frame = document.getElementById('deviceFrame');
            const island = document.getElementById('islandHeader');
            const punch = document.getElementById('pixelHeader');

            frame.className = type === 'iphone' ? 'device-iphone' : (type === 'pixel' ? 'device-pixel' : 'device-desktop');
            island.style.display = type === 'iphone' ? 'block' : 'none';
            punch.style.display = type === 'pixel' ? 'block' : 'none';
        }

        async function loadComponents() {
            const screen = '{{ $screen }}';
            const platform = document.getElementById('platformSelect').value;
            const version = document.getElementById('versionInput').value;
            const visitorId = document.getElementById('visitorInput').value;

            document.getElementById('badge-platform').textContent = 'Platform: ' + platform.toUpperCase();
            document.getElementById('badge-version').textContent = 'v' + version;
            document.getElementById('badge-visitor').textContent = 'Visitor: ' + visitorId;

            try {
                const url = `/api/ui/components/${screen}?platform=${encodeURIComponent(platform)}&app_version=${encodeURIComponent(version)}&visitor_id=${encodeURIComponent(visitorId)}`;
                const res = await fetch(url);
                const data = await res.json();
                currentPayload = data;

                document.getElementById('api-response').textContent = JSON.stringify(data, null, 2);

                // Update A/B Status Banner
                const expStatus = document.getElementById('badge-experiment-status');
                if (data.client_meta && data.client_meta.active_experiments_count > 0) {
                    const exp = data.client_meta.experiments[0];
                    expStatus.innerHTML = `<span class="badge bg-warning text-dark"><i class="bi bi-sliders"></i> A/B Active: ${exp.variant_name} (${exp.traffic_percent}% split)</span>`;
                } else {
                    expStatus.innerHTML = `<span class="badge bg-secondary"><i class="bi bi-check2"></i> Control Baseline</span>`;
                }

                renderComponents(data.components);
            } catch (err) {
                console.error(err);
                document.getElementById('ui-container').innerHTML = `<div class="alert alert-danger">Failed to fetch Server-Driven UI: ${err.message}</div>`;
            }
        }

        function renderComponents(components) {
            const container = document.getElementById('ui-container');
            container.innerHTML = '';

            if (!components || components.length === 0) {
                container.innerHTML = `
                    <div class="alert alert-info border-info text-center my-4">
                        <i class="bi bi-info-circle fs-3 d-block mb-2"></i>
                        No components match platform <strong>${document.getElementById('platformSelect').value}</strong> 
                        or version <strong>${document.getElementById('versionInput').value}</strong> on this screen.
                    </div>`;
                return;
            }

            components.forEach(comp => {
                const card = document.createElement('div');
                card.className = 'component-card';

                let variantBadge = '';
                if (comp.variant === 'variant_b') {
                    variantBadge = '<span class="badge bg-warning text-dark position-absolute top-0 end-0 m-2"><i class="bi bi-lightning-charge-fill"></i> A/B Variant B</span>';
                }

                let platformPills = (comp.platforms || ['all']).map(p => 
                    `<span class="badge bg-secondary me-1">${p.toUpperCase()}</span>`
                ).join('');

                switch(comp.type) {
                    case 'header':
                        card.innerHTML = `
                            ${variantBadge}
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="small text-muted"><i class="bi bi-type-h1"></i> Header Component</span>
                                <div>${platformPills}</div>
                            </div>
                            <h3 style="${comp.properties.style || 'color: #38bdf8;'}">${comp.properties.title || comp.name}</h3>
                            <p class="text-light opacity-75 mb-0">${comp.properties.subtitle || ''}</p>
                        `;
                        break;

                    case 'card':
                        const btnText = comp.properties.button_text || 'Interact';
                        const theme = comp.properties.theme || 'primary';
                        const badge = comp.properties.badge ? `<span class="badge bg-danger mb-2">${comp.properties.badge}</span><br>` : '';

                        card.innerHTML = `
                            ${variantBadge}
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="small text-muted"><i class="bi bi-card-heading"></i> Card Component</span>
                                <div>${platformPills}</div>
                            </div>
                            ${badge}
                            <h5 class="card-title text-${theme} fw-bold">${comp.properties.title || comp.name}</h5>
                            <p class="card-text text-light opacity-80">${comp.properties.content || 'Content powered by Server-Driven UI API.'}</p>
                            <button class="btn btn-sm btn-${theme} px-3" onclick="recordInteraction(${comp.id}, '${comp.variant || 'control'}')">
                                <i class="bi bi-cursor-fill"></i> ${btnText}
                            </button>
                        `;
                        break;

                    case 'button':
                        card.innerHTML = `
                            ${variantBadge}
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="small text-muted"><i class="bi bi-hand-index-thumb"></i> Action CTA</span>
                                <div>${platformPills}</div>
                            </div>
                            <button class="btn ${comp.properties.variant || 'btn-primary'} ${comp.properties.size || ''} w-100" 
                                    onclick="recordInteraction(${comp.id}, '${comp.variant || 'control'}')">
                                ${comp.properties.text || 'Submit'}
                            </button>
                        `;
                        break;

                    case 'form':
                        const fieldsHtml = comp.properties.fields ? comp.properties.fields.map(f => `
                            <div class="mb-2">
                                <label class="form-label small text-muted mb-1">${f.label}</label>
                                <input type="${f.type || 'text'}" class="form-control form-control-sm bg-dark text-light border-secondary" placeholder="${f.placeholder || ''}">
                            </div>
                        `).join('') : '<p class="text-muted small">No fields defined</p>';

                        card.innerHTML = `
                            ${variantBadge}
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="small text-muted"><i class="bi bi-ui-checks"></i> Form Component</span>
                                <div>${platformPills}</div>
                            </div>
                            <h6 class="text-info fw-bold mb-2">${comp.properties.title || 'Dynamic Form'}</h6>
                            <form onsubmit="event.preventDefault(); recordInteraction(${comp.id}, '${comp.variant || 'control'}');">
                                ${fieldsHtml}
                                <button type="submit" class="btn btn-sm btn-primary mt-2">Save Profile Data</button>
                            </form>
                        `;
                        break;

                    default:
                        card.innerHTML = `
                            ${variantBadge}
                            <h6 class="text-light">${comp.name}</h6>
                            <pre class="small text-muted">${JSON.stringify(comp.properties, null, 2)}</pre>
                        `;
                }

                container.appendChild(card);
            });
        }

        async function recordInteraction(componentId, variant) {
            const screen = '{{ $screen }}';
            const platform = document.getElementById('platformSelect').value;
            const visitorId = document.getElementById('visitorInput').value;

            try {
                const res = await fetch('/api/ui/telemetry/click', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        component_id: componentId,
                        screen: screen,
                        platform: platform,
                        variant: variant,
                        visitor_id: visitorId,
                    })
                });
                const data = await res.json();
                showToast(`Interaction recorded! Component #${componentId} (${variant}) ➔ Click Telemetry Saved.`);
            } catch (e) {
                console.error(e);
            }
        }

        function showToast(msg) {
            const toast = document.getElementById('toastNotification');
            document.getElementById('toastMsg').textContent = msg;
            toast.style.display = 'block';
            setTimeout(() => { toast.style.display = 'none'; }, 3500);
        }

        function copyRawJson() {
            if (currentPayload) {
                navigator.clipboard.writeText(JSON.stringify(currentPayload, null, 2));
                showToast('Server JSON schema copied to clipboard!');
            }
        }

        document.getElementById('btnRandomVisitor').addEventListener('click', () => {
            document.getElementById('visitorInput').value = 'vis_rnd_' + Math.floor(Math.random() * 8999 + 1000);
            loadComponents();
        });

        document.getElementById('btnApplyFilters').addEventListener('click', loadComponents);
        document.getElementById('platformSelect').addEventListener('change', loadComponents);

        document.addEventListener('DOMContentLoaded', loadComponents);
    </script>
</body>
</html>
