<?php

namespace Database\Seeds;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use App\Models\Admin;
use App\Models\User;

class AdminSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        if (!Admin::where('email', 'admin@gmail.com')->exists()) {
        	Admin::create([
                'name' => 'Admin',
                'email' => 'admin@gmail.com',
                'password' => Hash::make('admin@123'),
            ]);
        }        
    }
}
