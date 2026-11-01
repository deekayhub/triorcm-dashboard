<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Provider;
use App\Models\ProviderVerification;
use App\Models\ProviderLicense;
use App\Models\CredentialingStep;
use App\Models\ProviderApplication;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            SuperAdminSeeder::class,
            DisclosureQuestionSeeder::class,
        ]);

        Provider::factory()->count(12)->create();
        ProviderVerification::factory()->count(8)->create();
        ProviderLicense::factory()->count(10)->create();
        ProviderApplication::factory()->count(6)->create();
        CredentialingStep::factory()->count(12)->create();
    }
}
