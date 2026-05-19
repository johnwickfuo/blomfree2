<?php

namespace Database\Seeders;

use App\Models\ShippingZone;
use Illuminate\Database\Seeder;

class ShippingZoneSeeder extends Seeder
{
    public function run(): void
    {
        $zones = [
            [
                'name' => 'Bayelsa',
                'price' => 2000,
                'states' => ['Bayelsa'],
                'delivery_estimate_days' => '1-2 days',
                'order' => 1,
            ],
            [
                'name' => 'Neighboring South',
                'price' => 3500,
                'states' => ['Rivers', 'Delta', 'Edo', 'Akwa Ibom', 'Imo', 'Cross River'],
                'delivery_estimate_days' => '2-4 days',
                'order' => 2,
            ],
            [
                'name' => 'Other South & Middle Belt',
                'price' => 5000,
                'states' => [
                    'Lagos', 'Ogun', 'Oyo', 'Osun', 'Ondo', 'Ekiti',
                    'Anambra', 'Abia', 'Enugu', 'Ebonyi',
                    'Kogi', 'Benue', 'Nasarawa', 'Plateau', 'FCT',
                ],
                'delivery_estimate_days' => '3-5 days',
                'order' => 3,
            ],
            [
                'name' => 'North',
                'price' => 6500,
                'states' => [
                    'Kaduna', 'Kano', 'Katsina', 'Sokoto', 'Kebbi', 'Zamfara',
                    'Jigawa', 'Bauchi', 'Gombe', 'Adamawa', 'Taraba', 'Yobe',
                    'Borno', 'Niger', 'Kwara',
                ],
                'delivery_estimate_days' => '5-7 days',
                'order' => 4,
            ],
        ];

        foreach ($zones as $zone) {
            ShippingZone::updateOrCreate(
                ['name' => $zone['name']],
                array_merge($zone, ['is_active' => true]),
            );
        }
    }
}
