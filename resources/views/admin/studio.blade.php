<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Server-Driven UI Studio - Drag & Drop Canvas & Device Visualizer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background: #0b1120; color: #f1f5f9; font-family: system-ui, -apple-system, sans-serif; overflow-x: hidden; }
        .studio-header {
            background: #1e293b;
            border-bottom: 1px solid #334155;
            padding: 12px 24px;
        }
        .canvas-pane {
            background: #0f172a;
            min-height: calc(100vh - 140px);
            border-right: 1px solid #334155;
            padding: 20px;
        }
        .preview-pane {
            background: #020617;
            min-height: calc(100vh - 140px);
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding: 30px;
            overflow-y: auto;
        }
        .inspector-pane {
            background: #0f172a;
            min-height: calc(100vh - 140px);
            border-left: 1px solid #334155;
            padding: 20px;
        }
        .sortable-item {
            background: #1e293b;
            border: 1px solid #334155;
            border-radius: 10px;
            padding: 12px 16px;
            margin-bottom: 10px;
            cursor: grab;
            transition: all 0.2s ease;
        }
        .sortable-item:hover {
            border-color: #38bdf8;
            background: #24344d;
        }
        .sortable-item.dragging {
            opacity: 0.4;
            border: 2px dashed #38bdf8;
        }
        .sortable-item.selected {
            border-color: #38bdf8;
            box-shadow: 0 0 0 2px rgba(56, 189, 248, 0.4);
        }
        /* Realistic Device Shells */
        .device-iphone {
            width: 390px;
            min-height: 740px;
            border: 12px solid #334155;
            border-radius: 46px;
            background: #0b1120;
            padding: 18px 14px;
            box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.8);
            position: relative;
        }
        .dynamic-island {
            width: 110px;
            height: 24px;
            background: #000;
            border-radius: 20px;
            margin: 0 auto 16px auto;
        }
        .device-pixel {
            width: 400px;
            min-height: 740px;
            border: 10px solid #1e293b;
            border-radius: 38px;
            background: #0b1120;
            padding: 18px 14px;
            box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.8);
            position: relative;
        }
        .pixel-punchhole {
            width: 14px;
            height: 14px;
            background: #000;
            border-radius: 50%;
            margin: 0 auto 16px auto;
        }
        .device-desktop {
            width: 100%;
            max-width: 860px;
            background: #0b1120;
            border: 1px solid #334155;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.6);
        }
        .component-preview-card {
            background: #1e293b;
            border: 1px solid #334155;
            border-radius: 10px;
            padding: 16px;
            margin-bottom: 14px;
            position: relative;
        }
    </style>
</head>
<body>
    @include('layouts.navigation')

    <!-- Top Studio Toolbar -->
    <div class="studio-header d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-3">
            <span class="badge bg-primary px-3 py-2"><i class="bi bi-palette-fill"></i> Canvas Studio</span>
            <div class="btn-group btn-group-sm">
                @foreach($screens as $scr)
                    <a href="{{ route('admin.studio', ['screen' => $scr, 'platform' => $platform, 'device' => $deviceFrame]) }}" 
                       class="btn {{ $screen === $scr ? 'btn-primary' : 'btn-outline-secondary text-light' }}">
                        {{ ucfirst($scr) }}
                    </a>
                @endforeach
            </div>
            <span class="text-secondary small">| Target Platform:</span>
            <div class="btn-group btn-group-sm">
                <a href="{{ route('admin.studio', ['screen' => $screen, 'platform' => 'all', 'device' => $deviceFrame]) }}" class="btn {{ $platform === 'all' ? 'btn-info' : 'btn-outline-secondary text-light' }}">All</a>
                <a href="{{ route('admin.studio', ['screen' => $screen, 'platform' => 'web', 'device' => $deviceFrame]) }}" class="btn {{ $platform === 'web' ? 'btn-info' : 'btn-outline-secondary text-light' }}">Web</a>
                <a href="{{ route('admin.studio', ['screen' => $screen, 'platform' => 'ios', 'device' => $deviceFrame]) }}" class="btn {{ $platform === 'ios' ? 'btn-info' : 'btn-outline-secondary text-light' }}">iOS</a>
                <a href="{{ route('admin.studio', ['screen' => $screen, 'platform' => 'android', 'device' => $deviceFrame]) }}" class="btn {{ $platform === 'android' ? 'btn-info' : 'btn-outline-secondary text-light' }}">Android</a>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2">
            <span class="text-muted small">Device Shell:</span>
            <div class="btn-group btn-group-sm">
                <button class="btn {{ $deviceFrame === 'iphone' ? 'btn-primary' : 'btn-outline-secondary text-light' }}" onclick="setFrame('iphone')"><i class="bi bi-apple"></i> iPhone 16 Pro</button>
                <button class="btn {{ $deviceFrame === 'pixel' ? 'btn-primary' : 'btn-outline-secondary text-light' }}" onclick="setFrame('pixel')"><i class="bi bi-android2"></i> Pixel 9 Pro</button>
                <button class="btn {{ $deviceFrame === 'desktop' ? 'btn-primary' : 'btn-outline-secondary text-light' }}" onclick="setFrame('desktop')"><i class="bi bi-laptop"></i> Desktop</button>
            </div>
        </div>
    </div>

    <!-- 3-Column Studio Layout -->
    <div class="container-fluid px-0">
        <div class="row g-0">
            <!-- Left Column: Drag & Drop Component Tree -->
            <div class="col-lg-3 canvas-pane">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0 text-info fw-bold"><i class="bi bi-list-nested"></i> Layout Hierarchy</h6>
                    <span class="badge bg-secondary" id="dragStatus">Drag items to reorder</span>
                </div>

                <div id="sortableList">
                    @foreach($components as $index => $comp)
                        <div class="sortable-item d-flex justify-content-between align-items-center" 
                             draggable="true" 
                             data-id="{{ $comp->id }}" 
                             data-index="{{ $index }}"
                             onclick="selectComponent({{ json_encode($comp) }})">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-grip-vertical text-secondary"></i>
                                <div>
                                    <div class="fw-semibold text-light small">{{ $comp->name }}</div>
                                    <span class="badge bg-dark border border-secondary text-secondary" style="font-size: 10px;">{{ strtoupper($comp->type) }}</span>
                                    <span class="badge bg-secondary" style="font-size: 10px;">#{{ $comp->order }}</span>
                                </div>
                            </div>
                            <div>
                                @foreach($comp->platforms ?? ['all'] as $p)
                                    <span class="badge bg-dark text-info border border-info" style="font-size: 9px;">{{ strtoupper($p) }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="alert alert-dark border-secondary mt-3 small p-2">
                    <i class="bi bi-info-circle text-info"></i> Dragging an item immediately broadcasts new layout order to the server and refreshes the live frame.
                </div>
            </div>

            <!-- Center Column: Multi-Device Live Canvas -->
            <div class="col-lg-6 preview-pane">
                <div id="deviceShell" class="{{ $deviceFrame === 'iphone' ? 'device-iphone' : ($deviceFrame === 'pixel' ? 'device-pixel' : 'device-desktop') }}">
                    @if($deviceFrame === 'iphone')
                        <div class="dynamic-island" id="islandBar"></div>
                    @elseif($deviceFrame === 'pixel')
                        <div class="pixel-punchhole" id="punchBar"></div>
                    @endif

                    <div id="liveComponentsContainer">
                        @forelse($components as $comp)
                            <div class="component-preview-card" data-component-id="{{ $comp->id }}">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="badge bg-secondary" style="font-size: 10px;">{{ strtoupper($comp->type) }}</span>
                                    <span class="badge bg-dark border border-secondary text-light" style="font-size: 10px;">{{ implode(', ', $comp->platforms ?? ['all']) }}</span>
                                </div>

                                @if($comp->type === 'header')
                                    <h4 style="{{ $comp->properties['style'] ?? 'color: #38bdf8;' }}">{{ $comp->properties['title'] ?? $comp->name }}</h4>
                                    <p class="text-light opacity-75 small mb-0">{{ $comp->properties['subtitle'] ?? '' }}</p>
                                @elseif($comp->type === 'card')
                                    @if(!empty($comp->properties['badge']))
                                        <span class="badge bg-danger mb-1">{{ $comp->properties['badge'] }}</span>
                                    @endif
                                    <h6 class="text-{{ $comp->properties['theme'] ?? 'primary' }} fw-bold mb-1">{{ $comp->properties['title'] ?? $comp->name }}</h6>
                                    <p class="text-light opacity-80 small mb-2">{{ $comp->properties['content'] ?? 'Card content' }}</p>
                                    @if(!empty($comp->properties['button_text']))
                                        <button class="btn btn-sm btn-{{ $comp->properties['theme'] ?? 'primary' }}">{{ $comp->properties['button_text'] }}</button>
                                    @endif
                                @elseif($comp->type === 'button')
                                    <button class="btn {{ $comp->properties['variant'] ?? 'btn-primary' }} btn-sm w-100">{{ $comp->properties['text'] ?? 'Action' }}</button>
                                @elseif($comp->type === 'form')
                                    <h6 class="text-info fw-bold mb-2">{{ $comp->properties['title'] ?? 'Form' }}</h6>
                                    @foreach($comp->properties['fields'] ?? [] as $f)
                                        <div class="mb-1">
                                            <label class="text-muted" style="font-size: 10px;">{{ $f['label'] }}</label>
                                            <input type="text" class="form-control form-control-sm bg-dark text-light border-secondary" placeholder="{{ $f['placeholder'] ?? '' }}" disabled>
                                        </div>
                                    @endforeach
                                    <button class="btn btn-sm btn-primary mt-2" disabled>Submit</button>
                                @else
                                    <pre class="small text-muted mb-0">{{ json_encode($comp->properties) }}</pre>
                                @endif
                            </div>
                        @empty
                            <div class="text-center py-5 text-muted">
                                <i class="bi bi-box fs-1 d-block mb-2"></i>
                                No active components on this screen.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Right Column: Visual Property Inspector -->
            <div class="col-lg-3 inspector-pane">
                <h6 class="text-info fw-bold mb-3"><i class="bi bi-sliders2"></i> Visual Property Inspector</h6>

                <div id="emptyInspectorNotice" class="text-center text-muted py-5">
                    <i class="bi bi-hand-index-thumb fs-2 d-block mb-2"></i>
                    Click any component in the hierarchy or live canvas to inspect and edit properties visually.
                </div>

                <div id="inspectorForm" style="display: none;">
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold mb-1">COMPONENT IDENTIFIER</label>
                        <input type="text" id="inspectName" class="form-control form-control-sm bg-dark text-light border-secondary">
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold mb-1">MAIN TITLE / LABEL</label>
                        <input type="text" id="inspectTitle" class="form-control form-control-sm bg-dark text-light border-secondary">
                    </div>

                    <div class="mb-3" id="groupSubtitle">
                        <label class="form-label text-muted small fw-bold mb-1">SUBTITLE / CONTENT</label>
                        <textarea id="inspectContent" rows="3" class="form-control form-control-sm bg-dark text-light border-secondary"></textarea>
                    </div>

                    <div class="mb-3" id="groupButton">
                        <label class="form-label text-muted small fw-bold mb-1">BUTTON TEXT</label>
                        <input type="text" id="inspectButtonText" class="form-control form-control-sm bg-dark text-light border-secondary">
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold mb-1">TARGET PLATFORMS</label>
                        <div class="d-flex gap-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="platAll" value="all">
                                <label class="form-check-label small" for="platAll">All</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="platWeb" value="web">
                                <label class="form-check-label small" for="platWeb">Web</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="platIos" value="ios">
                                <label class="form-check-label small" for="platIos">iOS</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="platAndroid" value="android">
                                <label class="form-check-label small" for="platAndroid">Android</label>
                            </div>
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label text-muted small fw-bold mb-1">MIN APP VERSION</label>
                            <input type="text" id="inspectMinVersion" class="form-control form-control-sm bg-dark text-light border-secondary" placeholder="e.g. 2.0.0">
                        </div>
                        <div class="col-6">
                            <label class="form-label text-muted small fw-bold mb-1">STATUS</label>
                            <select id="inspectActive" class="form-select form-select-sm bg-dark text-light border-secondary">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>

                    <button class="btn btn-primary w-100 btn-sm fw-bold" onclick="saveComponentProperties()">
                        <i class="bi bi-save2"></i> Update Component
                    </button>
                    <div id="inspectorFeedback" class="small text-success text-center mt-2" style="display:none;"></div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let selectedComponent = null;

        function setFrame(type) {
            const shell = document.getElementById('deviceShell');
            shell.className = type === 'iphone' ? 'device-iphone' : (type === 'pixel' ? 'device-pixel' : 'device-desktop');
            const island = document.getElementById('islandBar');
            const punch = document.getElementById('punchBar');
            if (island) island.style.display = type === 'iphone' ? 'block' : 'none';
            if (punch) punch.style.display = type === 'pixel' ? 'block' : 'none';
        }

        // Drag and Drop Logic
        const sortableList = document.getElementById('sortableList');
        let draggedItem = null;

        sortableList.addEventListener('dragstart', (e) => {
            if (e.target.classList.contains('sortable-item')) {
                draggedItem = e.target;
                e.target.classList.add('dragging');
            }
        });

        sortableList.addEventListener('dragend', (e) => {
            if (e.target.classList.contains('sortable-item')) {
                e.target.classList.remove('dragging');
                saveNewOrder();
            }
        });

        sortableList.addEventListener('dragover', (e) => {
            e.preventDefault();
            const afterElement = getDragAfterElement(sortableList, e.clientY);
            if (afterElement == null) {
                sortableList.appendChild(draggedItem);
            } else {
                sortableList.insertBefore(draggedItem, afterElement);
            }
        });

        function getDragAfterElement(container, y) {
            const draggableElements = [...container.querySelectorAll('.sortable-item:not(.dragging)')];
            return draggableElements.reduce((closest, child) => {
                const box = child.getBoundingClientRect();
                const offset = y - box.top - box.height / 2;
                if (offset < 0 && offset > closest.offset) {
                    return { offset: offset, element: child };
                } else {
                    return closest;
                }
            }, { offset: Number.NEGATIVE_INFINITY }).element;
        }

        async function saveNewOrder() {
            const items = [...sortableList.querySelectorAll('.sortable-item')].map((el, idx) => ({
                id: el.getAttribute('data-id'),
                order: idx + 1
            }));

            document.getElementById('dragStatus').textContent = 'Persisting order...';
            try {
                const res = await fetch('/api/ui/components/reorder', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ items: items })
                });
                const data = await res.json();
                document.getElementById('dragStatus').textContent = 'Saved! Reloading frame...';
                setTimeout(() => { location.reload(); }, 600);
            } catch (err) {
                console.error(err);
                document.getElementById('dragStatus').textContent = 'Reorder error';
            }
        }

        // Inspector Logic
        function selectComponent(comp) {
            selectedComponent = comp;
            document.querySelectorAll('.sortable-item').forEach(el => el.classList.remove('selected'));
            const targetEl = document.querySelector(`.sortable-item[data-id="${comp.id}"]`);
            if (targetEl) targetEl.classList.add('selected');

            document.getElementById('emptyInspectorNotice').style.display = 'none';
            document.getElementById('inspectorForm').style.display = 'block';

            document.getElementById('inspectName').value = comp.name || '';
            document.getElementById('inspectTitle').value = (comp.properties && (comp.properties.title || comp.properties.text)) || comp.name;
            document.getElementById('inspectContent').value = (comp.properties && (comp.properties.content || comp.properties.subtitle)) || '';
            document.getElementById('inspectButtonText').value = (comp.properties && comp.properties.button_text) || '';
            document.getElementById('inspectMinVersion').value = comp.min_version || '';
            document.getElementById('inspectActive').value = comp.is_active ? '1' : '0';

            const plats = comp.platforms || ['all'];
            document.getElementById('platAll').checked = plats.includes('all');
            document.getElementById('platWeb').checked = plats.includes('web');
            document.getElementById('platIos').checked = plats.includes('ios');
            document.getElementById('platAndroid').checked = plats.includes('android');
        }

        async function saveComponentProperties() {
            if (!selectedComponent) return;

            const selectedPlatforms = [];
            if (document.getElementById('platAll').checked) selectedPlatforms.push('all');
            if (document.getElementById('platWeb').checked) selectedPlatforms.push('web');
            if (document.getElementById('platIos').checked) selectedPlatforms.push('ios');
            if (document.getElementById('platAndroid').checked) selectedPlatforms.push('android');

            const updatedProps = Object.assign({}, selectedComponent.properties);
            if (selectedComponent.type === 'button') {
                updatedProps.text = document.getElementById('inspectTitle').value;
            } else {
                updatedProps.title = document.getElementById('inspectTitle').value;
                if (selectedComponent.type === 'header') {
                    updatedProps.subtitle = document.getElementById('inspectContent').value;
                } else if (selectedComponent.type === 'card') {
                    updatedProps.content = document.getElementById('inspectContent').value;
                    updatedProps.button_text = document.getElementById('inspectButtonText').value;
                }
            }

            try {
                const res = await fetch(`/api/ui/components/${selectedComponent.id}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        name: document.getElementById('inspectName').value,
                        properties: updatedProps,
                        platforms: selectedPlatforms.length > 0 ? selectedPlatforms : ['all'],
                        min_version: document.getElementById('inspectMinVersion').value,
                        is_active: document.getElementById('inspectActive').value === '1'
                    })
                });

                const data = await res.json();
                const fb = document.getElementById('inspectorFeedback');
                fb.textContent = '✓ Updated! Refreshing Canvas...';
                fb.style.display = 'block';
                setTimeout(() => { location.reload(); }, 600);
            } catch (e) {
                console.error(e);
            }
        }
    </script>
</body>
</html>
