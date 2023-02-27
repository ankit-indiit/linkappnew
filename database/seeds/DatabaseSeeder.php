<?php

use Illuminate\Database\Seeder;
use App\Admin;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // $this->call(AdminSeeder::class);
        if (!Admin::where('email', 'admin@gmail.com')->exists()) {
            Admin::create([
                'name' => 'Admin',
                'email' => 'admin@gmail.com',
                'password' => Hash::make('admin@123'),
            ]);
        }  
    }
}
