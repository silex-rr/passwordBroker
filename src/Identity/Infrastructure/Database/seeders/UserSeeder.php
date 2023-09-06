<?php

namespace Identity\Infrastructure\Database\seeders;

use Identity\Domain\User\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::factory(30)->create();
    }
}
