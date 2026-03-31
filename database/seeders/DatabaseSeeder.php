<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'vendedor']);
        Role::firstOrCreate(['name' => 'comprador']);

        $pablo = User::factory()->create([
            'name' => 'Pablo',
            'email' => 'admin@curukicks.com',
        ]);
        $pablo->assignRole(['admin', 'vendedor']);
    }
}
