<?php

namespace Database\Seeders;

use App\Models\Cert;
use Illuminate\Database\Seeder;

class CertSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Cert::factory()->count(5)->create();
    }
}
