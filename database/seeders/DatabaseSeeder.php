<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            SettingSeeder::class,
            ShippingZoneSeeder::class,
            LandSeeder::class,
            AnimalSeeder::class,
            ProductSeeder::class,
            AffiliateSeeder::class,
            InstallmentSeeder::class,
        ]);
    }
}
