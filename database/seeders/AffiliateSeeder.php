<?php

namespace Database\Seeders;

use App\Models\Affiliate;
use App\Models\Animal;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AffiliateSeeder extends Seeder
{
    public function run(): void
    {
        // 2 sample affiliates so admins can play with the dashboards.
        $this->seedAffiliate([
            'name' => 'Chinedu Okeke',
            'email' => 'chinedu@example.com',
            'phone' => '08031234567',
            'whatsapp_number' => '08031234567',
            'social_handles' => ['instagram' => '@chinedushops'],
            'bank_name' => 'GTBank',
            'bank_account_number' => '0123456789',
            'bank_account_name' => 'CHINEDU OKEKE',
        ]);

        $this->seedAffiliate([
            'name' => 'Amaka Eze',
            'email' => 'amaka@example.com',
            'phone' => '08039876543',
            'whatsapp_number' => '08039876543',
            'social_handles' => ['tiktok' => '@amaka.style'],
        ]);

        // Enable affiliate pricing on a sample of existing items so the cart
        // discount flow is testable end-to-end.
        Product::query()
            ->where('subsidiary', 'collections')
            ->where('has_variants', false)
            ->limit(3)
            ->get()
            ->each(function (Product $p): void {
                $base = (float) $p->base_price;
                $p->update([
                    'affiliate_price' => $base * 0.95,
                    'affiliate_commission' => max(200, $base * 0.05),
                ]);
            });

        Product::query()
            ->where('subsidiary', 'gadgets')
            ->where('has_variants', false)
            ->limit(2)
            ->get()
            ->each(function (Product $p): void {
                $base = (float) $p->base_price;
                $p->update([
                    'affiliate_price' => $base * 0.97,
                    'affiliate_commission' => max(500, $base * 0.04),
                ]);
            });

        Animal::query()
            ->where('listing_type', 'pool')
            ->limit(2)
            ->get()
            ->each(function (Animal $a): void {
                $base = (float) $a->price;
                $a->update([
                    'affiliate_price' => $base * 0.95,
                    'affiliate_commission' => max(1000, $base * 0.05),
                ]);
            });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function seedAffiliate(array $data): void
    {
        $user = User::query()->updateOrCreate(
            ['email' => $data['email']],
            [
                'name' => $data['name'],
                'phone' => $data['phone'],
                'password' => Hash::make('password'),
                'is_admin' => false,
                'is_affiliate' => true,
            ],
        );

        Affiliate::query()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'status' => 'active',
                'whatsapp_number' => $data['whatsapp_number'] ?? null,
                'social_handles' => $data['social_handles'] ?? [],
                'bank_name' => $data['bank_name'] ?? null,
                'bank_account_number' => $data['bank_account_number'] ?? null,
                'bank_account_name' => $data['bank_account_name'] ?? null,
                'joined_at' => now(),
            ],
        );
    }
}
