<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'dsc-23@yandex.ru'],
            [
                'name' => 'Джон Уик',
                'password' => Hash::make('123123123'),
            ],
        );
    }
}