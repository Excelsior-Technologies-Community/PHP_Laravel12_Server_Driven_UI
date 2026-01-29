# PHP_Laravel12_Server_Driven_UI

## Project Overview

This project demonstrates a **Server-Driven UI architecture** using **Laravel**. Instead of hardcoding UI layouts on the frontend, all UI components (headers, cards, buttons, forms, etc.) are defined and managed on the server (database) and delivered via APIs. The frontend dynamically renders the UI based on server responses.

This approach allows UI changes **without redeploying frontend code**, making it ideal for dashboards, mobile apps, A/B testing, and feature toggles.

---

## Tech Stack

* PHP 8+
* Laravel (latest)
* MySQL
* Blade Templates
* Bootstrap 5
* REST APIs (JSON)

---

## Key Features

* Server-driven UI components stored in database
* Dynamic UI rendering using API responses
* Screen-based UI management (home, profile, dashboard, settings)
* Admin panel to manage UI components
* Enable or disable UI components in real time
* No frontend redeployment required for UI changes

---

## Project Setup

### Step 1: Create Laravel Project

```bash
composer create-project laravel/laravel server-driven-ui
cd server-driven-ui
```

---

### Step 2: Database Configuration

Update `.env` file:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=server_driven_ui
DB_USERNAME=root
DB_PASSWORD=
```

Create the database manually in MySQL:

```sql
CREATE DATABASE server_driven_ui;
```

---

## Database Design

### UI Components Table

Each UI element is stored as a record in the database.

Fields explanation:

* `type`: Component type (header, card, button, form)
* `name`: Component identifier
* `properties`: JSON-based dynamic configuration
* `screen`: Screen name (home, profile, dashboard)
* `order`: Display order
* `is_active`: Enable or disable component

---

## Models and Migrations

### Step 3: Create Model and Migration

```bash
php artisan make:model UIComponent -m
```

Migration structure:

```php
$table->id();
$table->string('type');
$table->string('name');
$table->json('properties');
$table->string('screen');
$table->integer('order')->default(0);
$table->boolean('is_active')->default(true);
$table->timestamps();
```

Run migration:

```bash
php artisan migrate
```

---

## Controller Logic

### Step 4: Create Controller

```bash
php artisan make:controller UIController
```

Controller Responsibilities:

* Fetch UI components for a screen
* Fetch available screens
* Admin management of components
* Enable or disable components

---

## Routing

### Step 5: Define Routes

```php
Route::get('/demo/{screen?}', [UIController::class, 'demo']);
Route::get('/admin', [UIController::class, 'admin']);

Route::get('/api/ui/components/{screen}', [UIController::class, 'getComponents']);
Route::get('/api/ui/screens', [UIController::class, 'getScreens']);
Route::post('/api/ui/components', [UIController::class, 'createComponent']);
Route::post('/api/ui/components/{id}/toggle', [UIController::class, 'toggleComponent']);
```

---

## Views

### Step 6: Demo Page

* Dynamically fetches UI components via API
* Renders UI based on component type
* Displays raw API response for learning

Supported components:

* Header
* Card
* Button
* Form

---

### Admin Panel

* Add new UI components
* Assign components to screens
* Define properties using JSON
* Activate or deactivate components

Admin panel allows **complete UI control without code changes**.

---

## Database Seeding

### Step 7: Create Seeder

```bash
php artisan make:seeder UIComponentsSeeder
```

Seeder adds sample components for:

* Home screen
* Profile screen
* Dashboard screen

Run seeder:

```bash
php artisan db:seed --class=UIComponentsSeeder
```

---

## Running the Project

### Step 8: Start Server

```bash
php artisan serve
```
### Screenshot
<img width="1752" height="966" alt="image" src="https://github.com/user-attachments/assets/672495d4-13ee-4692-88d9-d2d9a1f26000" />
<img width="1642" height="959" alt="image" src="https://github.com/user-attachments/assets/956a638a-70cc-406c-b80c-64bc41b0dd7d" />


---

## Application URLs

Demo Screens:

* [http://localhost:8000/demo/home](http://localhost:8000/demo/home)
* [http://localhost:8000/demo/profile](http://localhost:8000/demo/profile)
* [http://localhost:8000/demo/dashboard](http://localhost:8000/demo/dashboard)
* [http://localhost:8000/demo/settings](http://localhost:8000/demo/settings)

Admin Panel:

* [http://localhost:8000/admin](http://localhost:8000/admin)

---

## API Endpoints

| Method | Endpoint                       | Description               |
| ------ | ------------------------------ | ------------------------- |
| GET    | /api/ui/components/{screen}    | Get components for screen |
| GET    | /api/ui/screens                | List all screens          |
| POST   | /api/ui/components             | Create component          |
| POST   | /api/ui/components/{id}/toggle | Enable/Disable component  |

---

## Example API Response

```json
{
  "success": true,
  "screen": "home",
  "components": [
    {
      "id": 1,
      "type": "header",
      "name": "Main Header",
      "properties": {
        "title": "Welcome",
        "subtitle": "Server Driven UI"
      }
    }
  ]
}
```

---

## Why Server-Driven UI?

* Faster UI updates
* Feature toggling
* A/B testing support
* Ideal for mobile & SPA apps
* Centralized UI control

---

## Future Enhancements

* Authentication for admin panel
* Drag-and-drop UI ordering
* Role-based UI components
* React / Mobile app frontend
* Component versioning

---

## Conclusion

This project provides a **complete beginner-friendly implementation of Server-Driven UI using Laravel**. It shows how UI layouts can be fully controlled from the backend using APIs and JSON configuration.

Perfect for learning modern backend-driven UI architecture.

---

## Author

**Mihir Mehta**

PHP & Laravel Developer
