<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Super Admin
        User::updateOrCreate(
            ['email' => 'admin@damascusparking.com'],
            [
                'name' => 'مدير النظام',
                'role' => 'admin',
                'password' => Hash::make('admin123'),
            ]
        );

        // Operator
        User::updateOrCreate(
            ['email' => 'operator@damascusparking.com'],
            [
                'name' => 'مشغل الموقف',
                'role' => 'operator',
                'password' => Hash::make('operator123'),
            ]
        );

        echo "✅ Super Admin: admin@damascusparking.com / admin123\n";
        echo "✅ Operator: operator@damascusparking.com / operator123\n";
    }
}
?>

