<?php

namespace Database\Seeders;

use App\Models\UIComponent;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UIComponentsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Disable foreign key checks to avoid issues
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Clear existing data using delete instead of truncate
        UIComponent::query()->delete();

        // Reset auto increment
        DB::statement('ALTER TABLE ui_components AUTO_INCREMENT = 1;');

        // Sample components for home screen
        $components = [
            [
                'type' => 'header',
                'name' => 'Main Header',
                'screen' => 'home',
                'properties' => [
                    'title' => 'Welcome to Server-Driven UI',
                    'subtitle' => 'Dynamic UI powered by Laravel',
                ],
                'order' => 1,
                'is_active' => true,
            ],
            [
                'type' => 'card',
                'name' => 'Feature Card 1',
                'screen' => 'home',
                'properties' => [
                    'title' => 'Dynamic Components',
                    'content' => 'UI components are served from the server and can be updated without app deployment.',
                    'button_text' => 'Learn More',
                ],
                'order' => 2,
                'is_active' => true,
            ],
            [
                'type' => 'button',
                'name' => 'Primary Action',
                'screen' => 'home',
                'properties' => [
                    'text' => 'Get Started',
                    'variant' => 'btn-success',
                ],
                'order' => 3,
                'is_active' => true,
            ],
            [
                'type' => 'header',
                'name' => 'Profile Header',
                'screen' => 'profile',
                'properties' => [
                    'title' => 'User Profile',
                    'subtitle' => 'Manage your account settings',
                ],
                'order' => 1,
                'is_active' => true,
            ],
            [
                'type' => 'form',
                'name' => 'Profile Form',
                'screen' => 'profile',
                'properties' => [
                    'title' => 'Edit Profile',
                    'fields' => [
                        [
                            'label' => 'Full Name',
                            'type' => 'text',
                            'placeholder' => 'Enter your name',
                        ],
                        [
                            'label' => 'Email',
                            'type' => 'email',
                            'placeholder' => 'Enter your email',
                        ],
                        [
                            'label' => 'Bio',
                            'type' => 'textarea',
                            'placeholder' => 'Tell us about yourself',
                        ],
                    ],
                ],
                'order' => 2,
                'is_active' => true,
            ],
            [
                'type' => 'card',
                'name' => 'User Stats',
                'screen' => 'dashboard',
                'properties' => [
                    'title' => 'Dashboard Overview',
                    'content' => 'Your activity and statistics will appear here.',
                ],
                'order' => 1,
                'is_active' => true,
            ],
            [
                'type' => 'header',
                'name' => 'Settings Header',
                'screen' => 'settings',
                'properties' => [
                    'title' => 'Settings',
                    'subtitle' => 'Configure your application',
                ],
                'order' => 1,
                'is_active' => true,
            ],
            [
                'type' => 'card',
                'name' => 'Appearance Settings',
                'screen' => 'settings',
                'properties' => [
                    'title' => 'Appearance',
                    'content' => 'Customize the look and feel of your application.',
                    'button_text' => 'Change Theme',
                ],
                'order' => 2,
                'is_active' => true,
            ],
        ];

        foreach ($components as $component) {
            UIComponent::create($component);
        }

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->command->info('UI Components seeded successfully!');
    }
}
