<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Server Driven UI - Admin Dashboard</title>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">


    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"
        rel="stylesheet">


    <style>
        body {
            background: #f5f7fb;
        }


        .dashboard-header {

            background: linear-gradient(135deg,
                    #0d6efd,
                    #6610f2);

            color: white;

            padding: 25px;

            border-radius: 15px;

            margin-bottom: 25px;

        }



        .stat-card {

            border: none;

            border-radius: 15px;

            transition: 0.3s;

            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);

        }



        .stat-card:hover {

            transform: translateY(-5px);

        }



        .icon-box {

            width: 50px;

            height: 50px;

            border-radius: 50%;

            display: flex;

            align-items: center;

            justify-content: center;

            font-size: 22px;

        }



        .filter-card {

            border: none;

            border-radius: 15px;

            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);

        }



        .table-card {

            border: none;

            border-radius: 15px;

            overflow: hidden;

            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);

        }
    </style>


</head>


<body>


    <div class="container-fluid px-4 py-4">


        <!-- Header -->


        <div class="dashboard-header">


            <div class="d-flex justify-content-between align-items-center">


                <div>


                    <h1 class="mb-2">

                        <i class="bi bi-window-stack"></i>

                        Server Driven UI Dashboard

                    </h1>


                    <p class="mb-0">

                        Manage dynamic UI components from server

                    </p>


                </div>



                <div>


                    <a href="/"
                        class="btn btn-light">

                        <i class="bi bi-house"></i>

                        Home

                    </a>


                    <a href="{{ route('component.export') }}"
                        class="btn btn-success ms-2">

                        <i class="bi bi-file-earmark-spreadsheet"></i>

                        Export CSV

                    </a>


                    <i class="bi bi-eye"></i>

                    Demo

                    </a>


                </div>


            </div>


        </div>





        <!-- Statistics -->


        <div class="row g-4 mb-4">



            <div class="col-md-3">


                <div class="card stat-card p-3">


                    <div class="d-flex align-items-center">


                        <div class="icon-box bg-primary text-white">


                            <i class="bi bi-grid"></i>


                        </div>



                        <div class="ms-3">


                            <h6 class="text-muted mb-1">

                                Total Components

                            </h6>


                            <h3 class="mb-0">

                                {{ $statistics['total'] }}

                            </h3>


                        </div>


                    </div>


                </div>


            </div>





            <div class="col-md-3">


                <div class="card stat-card p-3">


                    <div class="d-flex align-items-center">


                        <div class="icon-box bg-success text-white">


                            <i class="bi bi-check-circle"></i>


                        </div>


                        <div class="ms-3">


                            <h6 class="text-muted mb-1">

                                Active

                            </h6>


                            <h3 class="mb-0">

                                {{ $statistics['active'] }}

                            </h3>


                        </div>


                    </div>


                </div>


            </div>





            <div class="col-md-3">


                <div class="card stat-card p-3">


                    <div class="d-flex align-items-center">


                        <div class="icon-box bg-danger text-white">


                            <i class="bi bi-x-circle"></i>


                        </div>


                        <div class="ms-3">


                            <h6 class="text-muted mb-1">

                                Inactive

                            </h6>


                            <h3 class="mb-0">

                                {{ $statistics['inactive'] }}

                            </h3>


                        </div>


                    </div>


                </div>


            </div>





            <div class="col-md-3">


                <div class="card stat-card p-3">


                    <div class="d-flex align-items-center">


                        <div class="icon-box bg-warning text-dark">


                            <i class="bi bi-display"></i>


                        </div>


                        <div class="ms-3">


                            <h6 class="text-muted mb-1">

                                Screens

                            </h6>


                            <h3 class="mb-0">

                                {{ $statistics['screens'] }}

                            </h3>


                        </div>


                    </div>


                </div>


            </div>



        </div>




        <!-- Add New Component -->

        <div class="card table-card p-4 mb-4">


            <div class="d-flex justify-content-between align-items-center mb-3">


                <h5 class="mb-0">

                    <i class="bi bi-plus-circle"></i>

                    Add New Component

                </h5>


            </div>



            <form id="createComponentForm">


                <div class="row g-3">


                    <div class="col-md-3">


                        <label class="form-label">

                            Component Type

                        </label>


                        <select name="type"
                            class="form-select"
                            required>


                            <option value="header">

                                Header

                            </option>


                            <option value="card">

                                Card

                            </option>


                            <option value="button">

                                Button

                            </option>


                            <option value="form">

                                Form

                            </option>


                        </select>


                    </div>




                    <div class="col-md-3">


                        <label class="form-label">

                            Component Name

                        </label>


                        <input type="text"
                            name="name"
                            class="form-control"
                            placeholder="Example: Welcome Card"
                            required>


                    </div>





                    <div class="col-md-3">


                        <label class="form-label">

                            Screen

                        </label>


                        <select name="screen"
                            class="form-select"
                            required>


                            <option value="home">

                                Home

                            </option>


                            <option value="profile">

                                Profile

                            </option>


                            <option value="dashboard">

                                Dashboard

                            </option>


                            <option value="settings">

                                Settings

                            </option>


                        </select>


                    </div>





                    <div class="col-md-3">


                        <label class="form-label">

                            Properties JSON

                        </label>


                        <textarea name="properties"
                            class="form-control"
                            rows="1"
                            required>{"title":"New Component"}</textarea>


                    </div>



                </div>



                <button type="submit"
                    class="btn btn-success mt-3">


                    <i class="bi bi-save"></i>

                    Save Component


                </button>



            </form>


        </div>

        <!-- Search Filter -->


        <div class="card filter-card p-4 mb-4">


            <h5 class="mb-3">


                <i class="bi bi-search"></i>

                Search & Filter Components


            </h5>



            <form method="GET"
                action="{{ route('admin') }}">


                <div class="row g-3">


                    <div class="col-md-5">


                        <input type="text"
                            name="search"
                            value="{{ request('search') }}"
                            class="form-control"
                            placeholder="Search component name, type, screen...">


                    </div>




                    <div class="col-md-3">


                        <select name="screen"
                            class="form-select">


                            <option value="">

                                All Screens

                            </option>


                            <option value="home"
                                {{ request('screen')=='home'?'selected':'' }}>

                                Home

                            </option>


                            <option value="profile"
                                {{ request('screen')=='profile'?'selected':'' }}>

                                Profile

                            </option>


                            <option value="dashboard"
                                {{ request('screen')=='dashboard'?'selected':'' }}>

                                Dashboard

                            </option>


                            <option value="settings"
                                {{ request('screen')=='settings'?'selected':'' }}>

                                Settings

                            </option>


                        </select>


                    </div>





                    <div class="col-md-2">


                        <select name="status"
                            class="form-select">


                            <option value="">

                                All Status

                            </option>


                            <option value="1"
                                {{ request('status')==='1'?'selected':'' }}>

                                Active

                            </option>


                            <option value="0"
                                {{ request('status')==='0'?'selected':'' }}>

                                Inactive

                            </option>


                        </select>


                    </div>





                    <div class="col-md-2">


                        <button class="btn btn-primary w-100">

                            <i class="bi bi-filter"></i>

                            Filter

                        </button>


                    </div>


                </div>



                <div class="mt-3">


                    <a href="{{ route('admin') }}"
                        class="btn btn-outline-secondary">


                        <i class="bi bi-arrow-clockwise"></i>

                        Reset


                    </a>


                </div>



            </form>



        </div>




        <!-- Component Table -->


        <div class="card table-card">


            <div class="card-header bg-white p-3">


                <h5 class="mb-0">

                    <i class="bi bi-list-task"></i>

                    UI Components List

                </h5>


            </div>




            <div class="table-responsive">


                <table class="table table-hover align-middle mb-0">


                    <thead class="table-light">


                        <tr>


                            <th>

                                ID

                            </th>


                            <th>

                                Type

                            </th>


                            <th>

                                Name

                            </th>


                            <th>

                                Screen

                            </th>


                            <th>

                                Properties

                            </th>


                            <th>

                                Status

                            </th>


                            <th>

                                Action

                            </th>


                        </tr>


                    </thead>




                    <tbody>



                        @forelse($components as $component)



                        <tr>


                            <td>

                                #{{ $component->id }}

                            </td>





                            <td>


                                <span class="badge bg-info text-dark">


                                    {{ ucfirst($component->type) }}


                                </span>


                            </td>





                            <td>


                                <strong>

                                    {{ $component->name }}

                                </strong>


                            </td>





                            <td>


                                <span class="badge bg-secondary">


                                    {{ ucfirst($component->screen) }}


                                </span>


                            </td>





                            <td>


                                <small>


                                    {{ json_encode($component->properties) }}


                                </small>


                            </td>





                            <td>


                                @if($component->is_active)


                                <span class="badge bg-success">


                                    Active


                                </span>


                                @else


                                <span class="badge bg-danger">


                                    Inactive


                                </span>


                                @endif


                            </td>



                            <td>



                                <div class="btn-group">

                                    <!-- Preview -->

                                    <button
                                        class="btn btn-sm btn-primary"
                                        onclick="previewComponent({{ $component->id }})"
                                        title="Preview">

                                        <i class="bi bi-eye"></i>

                                    </button>

                                    <!-- Duplicate -->

                                    <button
                                        class="btn btn-sm btn-info text-white"
                                        onclick="duplicateComponent({{ $component->id }})"
                                        title="Duplicate">

                                        <i class="bi bi-files"></i>

                                    </button>

                                    <!-- Toggle -->

                                    <button
                                        class="btn btn-sm btn-warning"
                                        onclick="toggleComponent({{ $component->id }})"
                                        title="Toggle">

                                        <i class="bi bi-arrow-repeat"></i>

                                    </button>

                                    <!-- Delete -->

                                    <button
                                        class="btn btn-sm btn-danger"
                                        onclick="deleteComponent({{ $component->id }})"
                                        title="Delete">

                                        <i class="bi bi-trash"></i>

                                    </button>

                                </div>



                            </td>



                        </tr>



                        @empty



                        <tr>


                            <td colspan="7"
                                class="text-center py-5">


                                <div class="text-muted">


                                    <i class="bi bi-inbox fs-1"></i>


                                    <h5>

                                        No Components Found

                                    </h5>


                                    <p>

                                        Try changing search/filter options.

                                    </p>


                                </div>


                            </td>


                        </tr>



                        @endforelse



                    </tbody>



                </table>


            </div>



        </div>







        <!-- Pagination -->


        <div class="mt-4">


            {{ $components->links('pagination::bootstrap-5') }}


        </div>

    </div>

    <!-- Preview Modal -->

    <div
        class="modal fade"
        id="previewModal"
        tabindex="-1">

        <div class="modal-dialog modal-lg">

            <div class="modal-content">

                <div class="modal-header">

                    <h5 class="modal-title">

                        <i class="bi bi-eye"></i>

                        Component Preview

                    </h5>

                    <button
                        class="btn-close"
                        data-bs-dismiss="modal">
                    </button>

                </div>

                <div class="modal-body">

                    <div id="previewContent">

                    </div>

                </div>

            </div>

        </div>

    </div>


    <!-- Bootstrap JS -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>



    <script>
        async function deleteComponent(id) {

            if (!confirm(
                    "Are you sure you want to delete this component?"
                )) {
                return;
            }


            try {

                let response = await fetch(
                    `/api/ui/components/${id}`, {

                        method: "DELETE",

                        headers: {

                            "Accept": "application/json",

                            "X-CSRF-TOKEN": "{{ csrf_token() }}"

                        }

                    }
                );


                let result = await response.json();



                if (result.success) {

                    alert(
                        "Component deleted successfully!"
                    );


                    window.location.reload();

                } else {

                    alert(
                        "Delete failed!"
                    );

                }


            } catch (error) {

                console.log(error);

                alert(
                    "Server error!"
                );

            }

        }
        // Create Component AJAX

        document
            .getElementById('createComponentForm')
            .addEventListener('submit', async function(e) {


                e.preventDefault();



                let form = e.target;


                let formData = new FormData(form);



                let data = Object.fromEntries(
                    formData.entries()
                );



                try {


                    let response = await fetch(
                        "{{ route('component.create') }}", {

                            method: "POST",

                            headers: {

                                "Content-Type": "application/json",

                                "Accept": "application/json",

                                "X-CSRF-TOKEN": "{{ csrf_token() }}"

                            },


                            body: JSON.stringify(data)

                        }
                    );



                    let result = await response.json();




                    if (result.success) {


                        alert(
                            "Component created successfully!"
                        );


                        window.location.reload();


                    } else {


                        alert(
                            "Something went wrong!"
                        );


                    }



                } catch (error) {


                    console.log(error);


                    alert(
                        "Server error occurred!"
                    );


                }



            });







        // Toggle Component Status


        async function toggleComponent(id) {


            if (!confirm(
                    "Are you sure you want to change status?"
                )) {


                return;


            }



            try {


                let response = await fetch(

                    `/api/ui/components/${id}/toggle`,

                    {


                        method: "POST",


                        headers: {


                            "Accept": "application/json",


                            "X-CSRF-TOKEN": "{{ csrf_token() }}"


                        }


                    }

                );



                let result = await response.json();




                if (result.success) {


                    alert(
                        "Component status updated!"
                    );


                    window.location.reload();


                } else {


                    alert(
                        "Unable to update status!"
                    );


                }



            } catch (error) {


                console.log(error);


                alert(
                    "Something went wrong!"
                );


            }



        }

        async function previewComponent(id) {

            try {

                let response = await fetch(`/api/ui/components/${id}/preview`);

                let result = await response.json();

                if (!result.success) {
                    alert("Unable to load preview.");
                    return;
                }

                let component = result.component;

                let html = "";

                switch (component.type) {

                    case "header":

                        html = `
                    <div class="text-center">
                        <h2>${component.properties.title ?? component.name}</h2>
                        <p class="text-muted">
                            ${component.properties.subtitle ?? ""}
                        </p>
                    </div>
                `;

                        break;

                    case "card":

                        html = `
                    <div class="card shadow-sm">

                        <div class="card-body">

                            <h4>
                                ${component.properties.title ?? component.name}
                            </h4>

                            <p>
                                ${component.properties.content ?? ""}
                            </p>

                            ${
                                component.properties.button_text
                                ?
                                `<button class="btn btn-primary">
                                    ${component.properties.button_text}
                                </button>`
                                :
                                ""
                            }

                        </div>

                    </div>
                `;

                        break;

                    case "button":

                        html = `
                    <button
                        class="btn ${component.properties.variant ?? 'btn-primary'}">

                        ${component.properties.text ?? "Button"}

                    </button>
                `;

                        break;

                    case "form":

                        html = `
                    <form>

                        <h4>
                            ${component.properties.title ?? "Form"}
                        </h4>

                        ${
                            component.properties.fields
                            ?
                            component.properties.fields.map(field => `
                                <div class="mb-3">

                                    <label class="form-label">

                                        ${field.label}

                                    </label>

                                    <input
                                        type="${field.type}"
                                        class="form-control"
                                        placeholder="${field.placeholder ?? ''}">
                                </div>
                            `).join("")
                            :
                            ""
                        }

                        <button class="btn btn-success">

                            Submit

                        </button>

                    </form>
                `;

                        break;

                    default:

                        html = `
                    <pre>
${JSON.stringify(component.properties, null, 4)}
                    </pre>
                `;
                }

                document.getElementById("previewContent").innerHTML = html;

                new bootstrap.Modal(
                    document.getElementById("previewModal")
                ).show();

            } catch (error) {

                console.log(error);

                alert("Preview failed.");

            }

        }

        async function duplicateComponent(id) {

            if (!confirm("Duplicate this component?")) {

                return;

            }

            try {

                let response = await fetch(

                    `/api/ui/components/${id}/duplicate`,

                    {

                        method: "POST",

                        headers: {

                            "Accept": "application/json",

                            "X-CSRF-TOKEN": "{{ csrf_token() }}"

                        }

                    }

                );

                let result = await response.json();

                if (result.success) {

                    alert(result.message);

                    location.reload();

                } else {

                    alert("Duplicate failed.");

                }

            } catch (error) {

                console.log(error);

                alert("Server Error");

            }

        }
    </script>


    <div class="card mt-4 p-4">


        <h5>

            <i class="bi bi-bar-chart"></i>

            Component Analytics

        </h5>


        <canvas id="componentChart"></canvas>


    </div>




    <script>
        const ctx = document
            .getElementById('componentChart');


        new Chart(ctx, {


            type: 'bar',


            data: {


                labels: [

                    'Headers',
                    'Cards',
                    'Buttons',
                    'Forms'

                ],


                datasets: [{

                    label: 'Components',


                    data: [

                        {
                            {
                                $chartData['headers']
                            }
                        },

                        {
                            {
                                $chartData['cards']
                            }
                        },

                        {
                            {
                                $chartData['buttons']
                            }
                        },

                        {
                            {
                                $chartData['forms']
                            }
                        }

                    ]

                }]


            },


            options: {


                responsive: true,


                plugins: {


                    legend: {


                        display: false


                    }


                }


            }


        });
    </script>

</body>

</html>