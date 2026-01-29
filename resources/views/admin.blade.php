<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage UI Components</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h1 class="mb-4">Manage UI Components</h1>
        
        <div class="mb-4">
            <a href="/" class="btn btn-secondary">Home</a>
            <a href="/demo/home" class="btn btn-primary ms-2">View Demo</a>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Add New Component</h5>
            </div>
            <div class="card-body">
                <form id="createComponentForm">
                    <div class="row">
                        <div class="col-md-3">
                            <label class="form-label">Type</label>
                            <select name="type" class="form-select" required>
                                <option value="header">Header</option>
                                <option value="card">Card</option>
                                <option value="button">Button</option>
                                <option value="form">Form</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Name</label>
                            <input type="text" name="name" class="form-control" required placeholder="Component Name">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Screen</label>
                            <select name="screen" class="form-select" required>
                                <option value="home">Home</option>
                                <option value="profile">Profile</option>
                                <option value="dashboard">Dashboard</option>
                                <option value="settings">Settings</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Properties (JSON)</label>
                            <textarea name="properties" class="form-control" rows="1" required>{"title": "Sample Title"}</textarea>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-success mt-3">Add Component</button>
                </form>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Type</th>
                        <th>Name</th>
                        <th>Screen</th>
                        <th>Properties</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($components as $component)
                    <tr>
                        <td>{{ $component->id }}</td>
                        <td><span class="badge bg-info">{{ $component->type }}</span></td>
                        <td>{{ $component->name }}</td>
                        <td><span class="badge bg-secondary">{{ $component->screen }}</span></td>
                        <td>
                            <small class="text-muted">
                                {{ json_encode($component->properties) }}
                            </small>
                        </td>
                        <td>
                            <span class="badge {{ $component->is_active ? 'bg-success' : 'bg-danger' }}">
                                {{ $component->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-warning toggle-btn" 
                                    data-id="{{ $component->id }}"
                                    onclick="toggleComponent({{ $component->id }})">
                                {{ $component->is_active ? 'Deactivate' : 'Activate' }}
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <script>
        document.getElementById('createComponentForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const formData = new FormData(e.target);
            const data = Object.fromEntries(formData.entries());
            
            try {
                const response = await fetch('/api/ui/components', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    alert('Component created successfully!');
                    window.location.reload();
                } else {
                    alert('Error creating component');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error creating component');
            }
        });

        async function toggleComponent(id) {
            try {
                const response = await fetch(`/api/ui/components/${id}/toggle`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });
                
                const result = await response.json();
                
                if (result.success) {
                    alert('Component status updated!');
                    window.location.reload();
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error updating component');
            }
        }
    </script>
</body>
</html>