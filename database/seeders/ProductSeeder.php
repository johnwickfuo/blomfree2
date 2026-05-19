<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = array_merge($this->collectionsProducts(), $this->gadgetsProducts());

        foreach ($products as $data) {
            $variants = $data['_variants'] ?? [];
            $imageLabels = $data['_images'];
            unset($data['_variants'], $data['_images']);

            $product = Product::create($data);

            foreach ($variants as $variant) {
                $product->variants()->create($variant);
            }

            foreach ($imageLabels as $label) {
                $product->addMedia($this->placeholderImage($label, $data['category'], $data['subsidiary']))
                    ->toMediaCollection('images');
            }
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function collectionsProducts(): array
    {
        return [
            [
                'name' => 'Premium Oxford Shirt',
                'slug' => 'premium-oxford-shirt',
                'subsidiary' => 'collections',
                'category' => 'Tops',
                'short_description' => 'A crisp, breathable cotton Oxford shirt that works for the office or the weekend.',
                'description' => 'Tailored from long-staple cotton with a button-down collar and a comfortable regular fit. A true wardrobe staple.',
                'base_price' => 18000,
                'compare_price' => 22000,
                'has_variants' => true,
                'attribute_keys' => ['size', 'color'],
                'is_featured' => true,
                'is_published' => true,
                'order' => 1,
                '_images' => ['Oxford Shirt', 'Oxford Shirt detail'],
                '_variants' => [
                    ['attributes' => ['size' => 'S', 'color' => 'White'], 'stock' => 6, 'sku' => null, 'price_override' => null],
                    ['attributes' => ['size' => 'S', 'color' => 'Blue'], 'stock' => 0, 'sku' => null, 'price_override' => null],
                    ['attributes' => ['size' => 'M', 'color' => 'White'], 'stock' => 10, 'sku' => null, 'price_override' => null],
                    ['attributes' => ['size' => 'M', 'color' => 'Blue'], 'stock' => 8, 'sku' => null, 'price_override' => null],
                    ['attributes' => ['size' => 'M', 'color' => 'Black'], 'stock' => 4, 'sku' => null, 'price_override' => null],
                    ['attributes' => ['size' => 'L', 'color' => 'White'], 'stock' => 7, 'sku' => null, 'price_override' => null],
                    ['attributes' => ['size' => 'L', 'color' => 'Black'], 'stock' => 0, 'sku' => null, 'price_override' => null],
                    ['attributes' => ['size' => 'XL', 'color' => 'Black'], 'stock' => 3, 'sku' => null, 'price_override' => null],
                ],
            ],
            [
                'name' => 'Leather Crossbody Bag',
                'slug' => 'leather-crossbody-bag',
                'subsidiary' => 'collections',
                'category' => 'Bags',
                'short_description' => 'A compact full-grain leather crossbody bag with an adjustable strap.',
                'description' => 'Handcrafted from full-grain leather with a roomy main compartment and an interior zip pocket. Ages beautifully with use.',
                'base_price' => 35000,
                'compare_price' => null,
                'has_variants' => false,
                'stock' => 14,
                'attribute_keys' => [],
                'is_featured' => false,
                'is_published' => true,
                'order' => 2,
                '_images' => ['Crossbody Bag', 'Crossbody Bag detail'],
            ],
            [
                'name' => 'Classic Sneakers',
                'slug' => 'classic-sneakers',
                'subsidiary' => 'collections',
                'category' => 'Footwear',
                'short_description' => 'Clean, low-profile sneakers that pair with almost anything.',
                'description' => 'A minimalist silhouette with a cushioned insole and durable rubber outsole. Built for everyday comfort.',
                'base_price' => 27000,
                'compare_price' => 32000,
                'has_variants' => true,
                'attribute_keys' => ['size', 'color'],
                'is_featured' => true,
                'is_published' => true,
                'order' => 3,
                '_images' => ['Classic Sneakers', 'Sneakers detail'],
                '_variants' => [
                    ['attributes' => ['size' => '40', 'color' => 'Black'], 'stock' => 5, 'sku' => null, 'price_override' => null],
                    ['attributes' => ['size' => '41', 'color' => 'Black'], 'stock' => 8, 'sku' => null, 'price_override' => null],
                    ['attributes' => ['size' => '42', 'color' => 'Black'], 'stock' => 0, 'sku' => null, 'price_override' => null],
                    ['attributes' => ['size' => '42', 'color' => 'White'], 'stock' => 6, 'sku' => null, 'price_override' => null],
                    ['attributes' => ['size' => '43', 'color' => 'White'], 'stock' => 4, 'sku' => null, 'price_override' => null],
                    ['attributes' => ['size' => '44', 'color' => 'White'], 'stock' => 0, 'sku' => null, 'price_override' => null],
                    ['attributes' => ['size' => '45', 'color' => 'Black'], 'stock' => 2, 'sku' => null, 'price_override' => null],
                ],
            ],
            [
                'name' => 'Gold-Tone Watch',
                'slug' => 'gold-tone-watch',
                'subsidiary' => 'collections',
                'category' => 'Watches',
                'short_description' => 'A slim gold-tone dress watch with a clean analogue dial.',
                'description' => 'Stainless steel case with a scratch-resistant mineral crystal and a quartz movement. Understated and versatile.',
                'base_price' => 45000,
                'compare_price' => 55000,
                'has_variants' => false,
                'stock' => 6,
                'attribute_keys' => [],
                'is_featured' => false,
                'is_published' => true,
                'order' => 4,
                '_images' => ['Gold-Tone Watch', 'Watch detail'],
            ],
            [
                'name' => 'Linen Summer Dress',
                'slug' => 'linen-summer-dress',
                'subsidiary' => 'collections',
                'category' => 'Dresses',
                'short_description' => 'A breezy midi dress in pure linen for warm days.',
                'description' => 'Lightweight linen with a relaxed fit, side pockets and a tie waist. Easy to dress up or down.',
                'base_price' => 24000,
                'compare_price' => null,
                'has_variants' => true,
                'attribute_keys' => ['size', 'color'],
                'is_featured' => false,
                'is_published' => true,
                'order' => 5,
                '_images' => ['Linen Dress', 'Linen Dress detail'],
                '_variants' => [
                    ['attributes' => ['size' => 'S', 'color' => 'Beige'], 'stock' => 5, 'sku' => null, 'price_override' => null],
                    ['attributes' => ['size' => 'M', 'color' => 'Beige'], 'stock' => 7, 'sku' => null, 'price_override' => null],
                    ['attributes' => ['size' => 'L', 'color' => 'Beige'], 'stock' => 0, 'sku' => null, 'price_override' => null],
                    ['attributes' => ['size' => 'S', 'color' => 'Navy'], 'stock' => 3, 'sku' => null, 'price_override' => null],
                    ['attributes' => ['size' => 'M', 'color' => 'Navy'], 'stock' => 6, 'sku' => null, 'price_override' => null],
                    ['attributes' => ['size' => 'L', 'color' => 'Navy'], 'stock' => 2, 'sku' => null, 'price_override' => null],
                ],
            ],
            [
                'name' => 'Denim Jacket',
                'slug' => 'denim-jacket',
                'subsidiary' => 'collections',
                'category' => 'Outerwear',
                'short_description' => 'A classic mid-wash denim jacket with a tailored fit.',
                'description' => 'Sturdy cotton denim with button cuffs and chest pockets. The all-season layer your wardrobe needs.',
                'base_price' => 28000,
                'compare_price' => null,
                'has_variants' => true,
                'attribute_keys' => ['size', 'color'],
                'is_featured' => false,
                'is_published' => true,
                'order' => 6,
                '_images' => ['Denim Jacket', 'Denim Jacket detail'],
                // Every variant shares the same price override — display_price should return 20000.
                '_variants' => [
                    ['attributes' => ['size' => 'M', 'color' => 'Blue'], 'stock' => 9, 'sku' => null, 'price_override' => 20000],
                    ['attributes' => ['size' => 'L', 'color' => 'Blue'], 'stock' => 5, 'sku' => null, 'price_override' => 20000],
                    ['attributes' => ['size' => 'XL', 'color' => 'Blue'], 'stock' => 0, 'sku' => null, 'price_override' => 20000],
                ],
            ],
            [
                'name' => 'Silk Scarf',
                'slug' => 'silk-scarf',
                'subsidiary' => 'collections',
                'category' => 'Accessories',
                'short_description' => 'A lightweight printed silk scarf with hand-rolled edges.',
                'description' => 'Pure silk with a soft drape and a vivid all-over print. A finishing touch for any outfit.',
                'base_price' => 9000,
                'compare_price' => null,
                'has_variants' => false,
                'stock' => 30,
                'attribute_keys' => [],
                'is_featured' => false,
                'is_published' => true,
                'order' => 7,
                '_images' => ['Silk Scarf', 'Silk Scarf detail'],
            ],
            [
                'name' => 'Canvas Tote Bag',
                'slug' => 'canvas-tote-bag',
                'subsidiary' => 'collections',
                'category' => 'Bags',
                'short_description' => 'A heavy-duty canvas tote for everyday carry.',
                'description' => 'Thick cotton canvas with reinforced handles and a flat base. Currently sold out — restock coming soon.',
                'base_price' => 7500,
                'compare_price' => null,
                'has_variants' => false,
                'stock' => 0,
                'attribute_keys' => [],
                'is_featured' => false,
                'is_published' => true,
                'order' => 8,
                '_images' => ['Canvas Tote', 'Canvas Tote detail'],
            ],
            [
                'name' => 'Slim-Fit Chinos',
                'slug' => 'slim-fit-chinos',
                'subsidiary' => 'collections',
                'category' => 'Bottoms',
                'short_description' => 'Versatile slim-fit chinos in a stretch cotton blend.',
                'description' => 'A modern slim cut with just enough stretch for comfort. Smart enough for the office, easy enough for the weekend.',
                'base_price' => 19500,
                'compare_price' => 24000,
                'has_variants' => true,
                'attribute_keys' => ['size', 'color'],
                'is_featured' => false,
                'is_published' => true,
                'order' => 9,
                '_images' => ['Slim-Fit Chinos', 'Chinos detail'],
                '_variants' => [
                    ['attributes' => ['size' => '30', 'color' => 'Khaki'], 'stock' => 4, 'sku' => null, 'price_override' => null],
                    ['attributes' => ['size' => '32', 'color' => 'Khaki'], 'stock' => 8, 'sku' => null, 'price_override' => null],
                    ['attributes' => ['size' => '34', 'color' => 'Khaki'], 'stock' => 0, 'sku' => null, 'price_override' => null],
                    ['attributes' => ['size' => '32', 'color' => 'Black'], 'stock' => 6, 'sku' => null, 'price_override' => null],
                    ['attributes' => ['size' => '34', 'color' => 'Black'], 'stock' => 5, 'sku' => null, 'price_override' => null],
                    ['attributes' => ['size' => '36', 'color' => 'Black'], 'stock' => 0, 'sku' => null, 'price_override' => null],
                ],
            ],
            [
                'name' => 'Aviator Sunglasses',
                'slug' => 'aviator-sunglasses',
                'subsidiary' => 'collections',
                'category' => 'Eyewear',
                'short_description' => 'Timeless aviator sunglasses with UV400 protection.',
                'description' => 'A lightweight metal frame with polarised, UV400-rated lenses. Comes with a hard case and cleaning cloth.',
                'base_price' => 14000,
                'compare_price' => 18000,
                'has_variants' => false,
                'stock' => 9,
                'attribute_keys' => [],
                'is_featured' => true,
                'is_published' => true,
                'order' => 10,
                '_images' => ['Aviator Sunglasses', 'Sunglasses detail'],
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function gadgetsProducts(): array
    {
        return [
            [
                'name' => 'iPhone 15 Pro',
                'slug' => 'iphone-15-pro',
                'subsidiary' => 'gadgets',
                'category' => 'Smartphones',
                'short_description' => 'Apple iPhone 15 Pro with the A17 Pro chip and a titanium design.',
                'description' => 'Featuring a 6.1-inch Super Retina XDR display, a pro camera system and USB-C. Sold with full warranty.',
                'base_price' => 1450000,
                'compare_price' => 1600000,
                'has_variants' => true,
                'attribute_keys' => ['storage', 'color'],
                'is_featured' => true,
                'is_published' => true,
                'order' => 1,
                '_images' => ['iPhone 15 Pro', 'iPhone 15 Pro back'],
                // Storage-tiered pricing — overrides differ, so display_price falls back to base_price.
                '_variants' => [
                    ['attributes' => ['storage' => '128GB', 'color' => 'Black'], 'stock' => 7, 'sku' => 'IP15P-128-BLK', 'price_override' => 1450000],
                    ['attributes' => ['storage' => '128GB', 'color' => 'White'], 'stock' => 4, 'sku' => 'IP15P-128-WHT', 'price_override' => 1450000],
                    ['attributes' => ['storage' => '256GB', 'color' => 'Black'], 'stock' => 5, 'sku' => 'IP15P-256-BLK', 'price_override' => 1620000],
                    ['attributes' => ['storage' => '256GB', 'color' => 'Natural'], 'stock' => 0, 'sku' => 'IP15P-256-NAT', 'price_override' => 1620000],
                    ['attributes' => ['storage' => '512GB', 'color' => 'Black'], 'stock' => 2, 'sku' => 'IP15P-512-BLK', 'price_override' => 1850000],
                    ['attributes' => ['storage' => '512GB', 'color' => 'Natural'], 'stock' => 0, 'sku' => 'IP15P-512-NAT', 'price_override' => 1850000],
                ],
            ],
            [
                'name' => 'PlayStation 5 Slim Disc Edition',
                'slug' => 'playstation-5-slim-disc-edition',
                'subsidiary' => 'gadgets',
                'category' => 'Consoles',
                'short_description' => 'The slimmer PS5 with a 4K Blu-ray disc drive and 1TB storage.',
                'description' => 'Next-generation gaming with lightning-fast loading, a DualSense wireless controller and ray-traced graphics.',
                'base_price' => 720000,
                'compare_price' => null,
                'has_variants' => false,
                'stock' => 5,
                'attribute_keys' => [],
                'is_featured' => true,
                'is_published' => true,
                'order' => 2,
                '_images' => ['PlayStation 5 Slim', 'PS5 controller'],
            ],
            [
                'name' => 'Sony WH-1000XM5 Headphones',
                'slug' => 'sony-wh-1000xm5-headphones',
                'subsidiary' => 'gadgets',
                'category' => 'Audio',
                'short_description' => 'Industry-leading noise-cancelling over-ear headphones.',
                'description' => 'Exceptional noise cancellation, 30-hour battery life and crystal-clear hands-free calling. Includes a carry case.',
                'base_price' => 320000,
                'compare_price' => 360000,
                'has_variants' => true,
                'attribute_keys' => ['color'],
                'is_featured' => false,
                'is_published' => true,
                'order' => 3,
                '_images' => ['Sony WH-1000XM5', 'Headphones detail'],
                '_variants' => [
                    ['attributes' => ['color' => 'Black'], 'stock' => 9, 'sku' => 'WH1000XM5-BLK', 'price_override' => null],
                    ['attributes' => ['color' => 'Silver'], 'stock' => 0, 'sku' => 'WH1000XM5-SLV', 'price_override' => null],
                ],
            ],
            [
                'name' => 'Apple Watch Series 9',
                'slug' => 'apple-watch-series-9',
                'subsidiary' => 'gadgets',
                'category' => 'Wearables',
                'short_description' => 'Apple Watch Series 9 with the S9 chip and a brighter display.',
                'description' => 'Advanced health and fitness tracking, the new double-tap gesture and a vivid Always-On Retina display.',
                'base_price' => 540000,
                'compare_price' => null,
                'has_variants' => true,
                'attribute_keys' => ['size', 'color'],
                'is_featured' => false,
                'is_published' => true,
                'order' => 4,
                '_images' => ['Apple Watch Series 9', 'Apple Watch detail'],
                '_variants' => [
                    ['attributes' => ['size' => '41mm', 'color' => 'Midnight'], 'stock' => 6, 'sku' => 'AWS9-41-MID', 'price_override' => 540000],
                    ['attributes' => ['size' => '41mm', 'color' => 'Starlight'], 'stock' => 3, 'sku' => 'AWS9-41-STL', 'price_override' => 540000],
                    ['attributes' => ['size' => '45mm', 'color' => 'Midnight'], 'stock' => 4, 'sku' => 'AWS9-45-MID', 'price_override' => 580000],
                    ['attributes' => ['size' => '45mm', 'color' => 'Starlight'], 'stock' => 0, 'sku' => 'AWS9-45-STL', 'price_override' => 580000],
                ],
            ],
            [
                'name' => 'Samsung Galaxy S24 Ultra',
                'slug' => 'samsung-galaxy-s24-ultra',
                'subsidiary' => 'gadgets',
                'category' => 'Smartphones',
                'short_description' => 'Samsung Galaxy S24 Ultra with a built-in S Pen and a 200MP camera.',
                'description' => 'A 6.8-inch Dynamic AMOLED display, titanium frame and powerful AI features. Sold with full warranty.',
                'base_price' => 1380000,
                'compare_price' => 1500000,
                'has_variants' => true,
                'attribute_keys' => ['storage', 'color'],
                'is_featured' => true,
                'is_published' => true,
                'order' => 5,
                '_images' => ['Galaxy S24 Ultra', 'Galaxy S24 Ultra back'],
                '_variants' => [
                    ['attributes' => ['storage' => '256GB', 'color' => 'Black'], 'stock' => 5, 'sku' => 'S24U-256-BLK', 'price_override' => 1380000],
                    ['attributes' => ['storage' => '256GB', 'color' => 'Gray'], 'stock' => 3, 'sku' => 'S24U-256-GRY', 'price_override' => 1380000],
                    ['attributes' => ['storage' => '512GB', 'color' => 'Black'], 'stock' => 2, 'sku' => 'S24U-512-BLK', 'price_override' => 1560000],
                    ['attributes' => ['storage' => '512GB', 'color' => 'Gray'], 'stock' => 0, 'sku' => 'S24U-512-GRY', 'price_override' => 1560000],
                ],
            ],
            [
                'name' => 'MacBook Air M3',
                'slug' => 'macbook-air-m3',
                'subsidiary' => 'gadgets',
                'category' => 'Laptops',
                'short_description' => 'The MacBook Air with the M3 chip — fast, silent and ultra-portable.',
                'description' => 'A 13.6-inch Liquid Retina display, up to 18 hours of battery life and a fanless design. Sold with full warranty.',
                'base_price' => 1250000,
                'compare_price' => null,
                'has_variants' => true,
                'attribute_keys' => ['storage', 'color'],
                'is_featured' => false,
                'is_published' => true,
                'order' => 6,
                '_images' => ['MacBook Air M3', 'MacBook Air detail'],
                '_variants' => [
                    ['attributes' => ['storage' => '256GB', 'color' => 'Silver'], 'stock' => 4, 'sku' => 'MBA-M3-256-SLV', 'price_override' => 1250000],
                    ['attributes' => ['storage' => '256GB', 'color' => 'Midnight'], 'stock' => 3, 'sku' => 'MBA-M3-256-MID', 'price_override' => 1250000],
                    ['attributes' => ['storage' => '512GB', 'color' => 'Silver'], 'stock' => 0, 'sku' => 'MBA-M3-512-SLV', 'price_override' => 1450000],
                    ['attributes' => ['storage' => '512GB', 'color' => 'Midnight'], 'stock' => 2, 'sku' => 'MBA-M3-512-MID', 'price_override' => 1450000],
                ],
            ],
            [
                'name' => 'Anker Power Bank 20000mAh',
                'slug' => 'anker-power-bank-20000mah',
                'subsidiary' => 'gadgets',
                'category' => 'Power',
                'short_description' => 'A high-capacity 20000mAh power bank with fast charging.',
                'description' => 'Charges most phones four to five times over, with USB-C Power Delivery and dual-port output.',
                'base_price' => 28000,
                'compare_price' => null,
                'has_variants' => false,
                'stock' => 40,
                'attribute_keys' => [],
                'is_featured' => false,
                'is_published' => true,
                'order' => 7,
                '_images' => ['Anker Power Bank', 'Power Bank detail'],
            ],
            [
                'name' => 'JBL Flip 6 Speaker',
                'slug' => 'jbl-flip-6-speaker',
                'subsidiary' => 'gadgets',
                'category' => 'Audio',
                'short_description' => 'A portable waterproof Bluetooth speaker with bold sound.',
                'description' => 'IP67 waterproof and dustproof with 12 hours of playtime. Currently sold out — restock coming soon.',
                'base_price' => 65000,
                'compare_price' => null,
                'has_variants' => false,
                'stock' => 0,
                'attribute_keys' => [],
                'is_featured' => false,
                'is_published' => true,
                'order' => 8,
                '_images' => ['JBL Flip 6', 'JBL Flip 6 detail'],
            ],
            [
                'name' => 'Logitech MX Master 3S Mouse',
                'slug' => 'logitech-mx-master-3s-mouse',
                'subsidiary' => 'gadgets',
                'category' => 'Accessories',
                'short_description' => 'A precision wireless mouse built for productivity.',
                'description' => 'Quiet clicks, an 8K DPI sensor and the MagSpeed scroll wheel. Works across multiple devices and operating systems.',
                'base_price' => 52000,
                'compare_price' => 60000,
                'has_variants' => false,
                'stock' => 18,
                'attribute_keys' => [],
                'is_featured' => false,
                'is_published' => true,
                'order' => 9,
                '_images' => ['MX Master 3S', 'MX Master 3S detail'],
            ],
            [
                'name' => 'iPad 10th Gen',
                'slug' => 'ipad-10th-gen',
                'subsidiary' => 'gadgets',
                'category' => 'Tablets',
                'short_description' => 'The 10th-generation iPad with a 10.9-inch Liquid Retina display.',
                'description' => 'A colourful all-screen design, the A14 Bionic chip and USB-C. Great for work, study and play.',
                'base_price' => 480000,
                'compare_price' => null,
                'has_variants' => true,
                'attribute_keys' => ['storage', 'color'],
                'is_featured' => false,
                'is_published' => false,
                'order' => 10,
                '_images' => ['iPad 10th Gen', 'iPad detail'],
                '_variants' => [
                    ['attributes' => ['storage' => '64GB', 'color' => 'Blue'], 'stock' => 6, 'sku' => 'IPAD10-64-BLU', 'price_override' => 480000],
                    ['attributes' => ['storage' => '64GB', 'color' => 'Silver'], 'stock' => 4, 'sku' => 'IPAD10-64-SLV', 'price_override' => 480000],
                    ['attributes' => ['storage' => '256GB', 'color' => 'Blue'], 'stock' => 0, 'sku' => 'IPAD10-256-BLU', 'price_override' => 620000],
                    ['attributes' => ['storage' => '256GB', 'color' => 'Silver'], 'stock' => 3, 'sku' => 'IPAD10-256-SLV', 'price_override' => 620000],
                ],
            ],
        ];
    }

    /**
     * Generate a placeholder JPG and return its temporary path.
     */
    private function placeholderImage(string $label, string $category, string $subsidiary): string
    {
        $bg = $subsidiary === 'gadgets' ? [28, 34, 44] : [44, 36, 40];

        $size = 1000;
        $image = imagecreatetruecolor($size, $size);

        $base = imagecolorallocate($image, $bg[0], $bg[1], $bg[2]);
        imagefilledrectangle($image, 0, 0, $size, $size, $base);

        $lighten = imagecolorallocate($image, min(255, $bg[0] + 24), min(255, $bg[1] + 24), min(255, $bg[2] + 24));
        for ($i = 0; $i < 12; $i++) {
            imagefilledpolygon($image, [
                $size - ($i * 150), 0,
                $size - ($i * 150) + 70, 0,
                $size - ($i * 150) - 240, $size,
                $size - ($i * 150) - 310, $size,
            ], $lighten);
        }

        $shade = imagecolorallocatealpha($image, 14, 12, 12, 28);
        imagefilledrectangle($image, 0, $size - 230, $size, $size, $shade);

        $orange = imagecolorallocate($image, 245, 130, 32);
        imagefilledrectangle($image, 70, $size - 178, 160, $size - 164, $orange);

        $white = imagecolorallocate($image, 255, 255, 255);
        $cream = imagecolorallocate($image, 232, 224, 216);
        imagestring($image, 5, 70, $size - 150, $label, $white);
        imagestring($image, 4, 70, $size - 118, $category, $cream);
        imagestring($image, 2, 70, 48, 'BLOMFREE '.strtoupper($subsidiary).'  -  PLACEHOLDER IMAGE', $orange);

        $path = storage_path('app/seed-product-'.Str::random(12).'.jpg');
        imagejpeg($image, $path, 85);
        imagedestroy($image);

        return $path;
    }
}
