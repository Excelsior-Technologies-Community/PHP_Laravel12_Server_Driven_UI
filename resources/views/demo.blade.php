<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Server-Driven UI Demo - {{ ucfirst($screen) }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .component-card {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            transition: all 0.3s;
        }
        .component-card:hover {
            border-color: #0d6efd;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .screen-selector {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h1 class="mb-4">Server-Driven UI Demo</h1>
        
        <div class="screen-selector">
            <h4>Select Screen:</h4>
            <div class="btn-group" role="group">
                <a href="{{ route('demo', ['screen' => 'home']) }}" 
                   class="btn btn-outline-primary {{ $screen == 'home' ? 'active' : '' }}">
                   Home
                </a>
                <a href="{{ route('demo', ['screen' => 'profile']) }}" 
                   class="btn btn-outline-primary {{ $screen == 'profile' ? 'active' : '' }}">
                   Profile
                </a>
                <a href="{{ route('demo', ['screen' => 'dashboard']) }}" 
                   class="btn btn-outline-primary {{ $screen == 'dashboard' ? 'active' : '' }}">
                   Dashboard
                </a>
                <a href="{{ route('demo', ['screen' => 'settings']) }}" 
                   class="btn btn-outline-primary {{ $screen == 'settings' ? 'active' : '' }}">
                   Settings
                </a>
            </div>
            <a href="{{ route('admin') }}" class="btn btn-success ms-3">Manage Components</a>
        </div>

        <div id="ui-container" class="mt-4">
            <div class="text-center">
                <div class="spinner-border" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p>Loading components...</p>
            </div>
        </div>

        <div class="mt-4">
            <h4>Raw API Response:</h4>
            <pre id="api-response" class="bg-light p-3 rounded"></pre>
        </div>
    </div>

    <script>
        async function loadComponents(screen) {
            try {
                const response = await fetch(`/api/ui/components/${screen}`);
                const data = await response.json();
                
                // Display raw response
                document.getElementById('api-response').textContent = 
                    JSON.stringify(data, null, 2);
                
                // Render components
                renderComponents(data.components);
            } catch (error) {
                console.error('Error loading components:', error);
                document.getElementById('ui-container').innerHTML = 
                    `<div class="alert alert-danger">Error loading components: ${error.message}</div>`;
            }
        }

        function renderComponents(components) {
            const container = document.getElementById('ui-container');
            
            if (!components || components.length === 0) {
                container.innerHTML = '<div class="alert alert-info">No components found for this screen.</div>';
                return;
            }

            container.innerHTML = '';
            
            components.forEach(component => {
                const element = createComponentElement(component);
                container.appendChild(element);
            });
        }

        function createComponentElement(component) {
            const div = document.createElement('div');
            div.className = 'component-card';
            div.setAttribute('data-component-id', component.id);
            
            switch(component.type) {
                case 'header':
                    div.innerHTML = `
                        <h3 class="text-primary">${component.properties.title || component.name}</h3>
                        <p class="text-muted">${component.properties.subtitle || ''}</p>
                    `;
                    break;
                    
                case 'card':
                    div.innerHTML = `
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">${component.properties.title || component.name}</h5>
                                <p class="card-text">${component.properties.content || 'No content provided.'}</p>
                                ${component.properties.button_text ? 
                                    `<button class="btn btn-primary">${component.properties.button_text}</button>` : ''}
                            </div>
                        </div>
                    `;
                    break;
                    
                case 'button':
                    div.innerHTML = `
                        <button class="btn ${component.properties.variant || 'btn-secondary'}" 
                                style="${component.properties.style || ''}">
                            ${component.properties.text || 'Click Me'}
                        </button>
                    `;
                    break;
                    
                case 'form':
                    div.innerHTML = `
                        <form>
                            <h5>${component.properties.title || 'Form'}</h5>
                            ${component.properties.fields ? 
                                component.properties.fields.map(field => `
                                    <div class="mb-3">
                                        <label class="form-label">${field.label}</label>
                                        <input type="${field.type || 'text'}" 
                                               class="form-control" 
                                               placeholder="${field.placeholder || ''}">
                                    </div>
                                `).join('') : 
                                '<p>No form fields defined.</p>'}
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </form>
                    `;
                    break;
                    
                default:
                    div.innerHTML = `
                        <h4>${component.name}</h4>
                        <p>Type: ${component.type}</p>
                        <pre>${JSON.stringify(component.properties, null, 2)}</pre>
                    `;
            }
            
            return div;
        }

        // Load components for current screen
        document.addEventListener('DOMContentLoaded', () => {
            loadComponents('{{ $screen }}');
        });
    </script>
</body>
</html>