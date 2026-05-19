<?php

namespace Database\Seeders;

use App\Models\Animal;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AnimalSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->animals() as $data) {
            $media = $data['_media'];
            unset($data['_media']);

            $animal = Animal::create($data);

            $animal->addMedia($this->placeholderImage($media['cover'], $data['breed'], $data['category']))
                ->toMediaCollection('cover');

            foreach ($media['gallery'] as $caption) {
                $animal->addMedia($this->placeholderImage($caption, $data['breed'], $data['category']))
                    ->toMediaCollection('gallery');
            }
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function animals(): array
    {
        return [
            // Individual dogs
            [
                'name' => 'Rex',
                'slug' => 'rex-german-shepherd',
                'listing_type' => 'individual',
                'category' => 'dog',
                'breed' => 'German Shepherd',
                'description' => 'A confident, well-socialised German Shepherd puppy from imported working lineage. Rex is alert, eager to learn and already responding to basic commands.',
                'origin' => 'Imported from Germany',
                'sex' => 'male',
                'age_text' => '4 months',
                'date_of_birth' => now()->subMonths(4)->toDateString(),
                'typical_adult_size' => 'Large (30-40kg)',
                'temperament' => 'Confident, loyal and highly trainable',
                'vaccination_status' => ['first_shots', 'dewormed'],
                'parents_info' => 'Sire and dam both imported, hip-scored working line.',
                'price' => 850000,
                'availability' => 'available',
                'supports_inspection' => true,
                'supports_online_purchase' => false,
                'highlights' => ['Imported working lineage', 'Vet-checked & dewormed', 'Crate introduced', 'Microchip ready'],
                'is_featured' => true,
                'order' => 1,
                '_media' => ['cover' => 'Rex', 'gallery' => ['Rex playing', 'Rex profile']],
            ],
            [
                'name' => 'Bella',
                'slug' => 'bella-rottweiler',
                'listing_type' => 'individual',
                'category' => 'dog',
                'breed' => 'Rottweiler',
                'description' => 'A calm and protective Rottweiler female with an excellent temperament. Bella has completed her core vaccinations and is ready for an experienced home.',
                'origin' => 'Locally bred from imported lineage',
                'sex' => 'female',
                'age_text' => '6 months',
                'date_of_birth' => now()->subMonths(6)->toDateString(),
                'typical_adult_size' => 'Large (35-45kg)',
                'temperament' => 'Calm, steady and protective',
                'vaccination_status' => ['first_shots', 'second_shots', 'rabies', 'dewormed'],
                'parents_info' => 'Dam on site for viewing; sire imported.',
                'price' => 700000,
                'availability' => 'available',
                'supports_inspection' => true,
                'supports_online_purchase' => false,
                'highlights' => ['Fully vaccinated', 'Excellent temperament', 'Dam available to view', 'Health guarantee'],
                'is_featured' => false,
                'order' => 2,
                '_media' => ['cover' => 'Bella', 'gallery' => ['Bella sitting', 'Bella outdoors']],
            ],

            // Individual cats
            [
                'name' => 'Luna',
                'slug' => 'luna-persian',
                'listing_type' => 'individual',
                'category' => 'cat',
                'breed' => 'Persian',
                'description' => 'A gentle, quiet Persian kitten with a dense silver coat. Luna is litter-trained and very comfortable with handling.',
                'origin' => 'Imported from Turkey',
                'sex' => 'female',
                'age_text' => '3 months',
                'date_of_birth' => now()->subMonths(3)->toDateString(),
                'typical_adult_size' => 'Small to medium',
                'temperament' => 'Gentle, calm and affectionate',
                'vaccination_status' => ['first_shots', 'dewormed'],
                'parents_info' => 'Pedigree imported parents.',
                'price' => 320000,
                'availability' => 'available',
                'supports_inspection' => true,
                'supports_online_purchase' => true,
                'highlights' => ['Imported pedigree', 'Litter-trained', 'Vet-checked', 'Grooming starter pack included'],
                'is_featured' => true,
                'order' => 3,
                '_media' => ['cover' => 'Luna', 'gallery' => ['Luna resting', 'Luna close-up']],
            ],
            [
                'name' => 'Simba',
                'slug' => 'simba-maine-coon',
                'listing_type' => 'individual',
                'category' => 'cat',
                'breed' => 'Maine Coon',
                'description' => 'A friendly, playful Maine Coon male with the breed\'s signature size and tufted ears. Simba is sociable and great with children.',
                'origin' => 'Locally bred',
                'sex' => 'male',
                'age_text' => '5 months',
                'date_of_birth' => now()->subMonths(5)->toDateString(),
                'typical_adult_size' => 'Large',
                'temperament' => 'Friendly, playful and sociable',
                'vaccination_status' => ['first_shots', 'second_shots', 'dewormed'],
                'parents_info' => 'Both parents on site.',
                'price' => 450000,
                'availability' => 'reserved',
                'supports_inspection' => true,
                'supports_online_purchase' => true,
                'highlights' => ['Gentle giant breed', 'Great with children', 'Fully dewormed', 'Health guarantee'],
                'is_featured' => false,
                'order' => 4,
                '_media' => ['cover' => 'Simba', 'gallery' => ['Simba playing', 'Simba portrait']],
            ],

            // Pool rabbits
            [
                'name' => 'New Zealand White Rabbit',
                'slug' => 'new-zealand-white-rabbit',
                'listing_type' => 'pool',
                'category' => 'rabbit',
                'breed' => 'New Zealand White',
                'description' => 'Fast-growing meat breed, ideal for both starters and commercial keepers. Sold from a healthy, well-managed colony.',
                'origin' => 'Locally bred',
                'sex' => 'mixed',
                'typical_adult_size' => 'Medium (4-5kg)',
                'vaccination_status' => ['dewormed'],
                'price' => 12000,
                'stock' => 24,
                'supports_inspection' => true,
                'supports_online_purchase' => true,
                'highlights' => ['Fast-growing meat breed', 'Healthy colony', 'Dewormed', 'Starter guidance included'],
                'is_featured' => true,
                'order' => 5,
                '_media' => ['cover' => 'New Zealand White', 'gallery' => ['Rabbit hutch', 'Colony view']],
            ],
            [
                'name' => 'Chinchilla Rabbit',
                'slug' => 'chinchilla-rabbit',
                'listing_type' => 'pool',
                'category' => 'rabbit',
                'breed' => 'Chinchilla',
                'description' => 'A hardy dual-purpose breed prized for its dense coat and steady growth. Good temperament and easy to manage.',
                'origin' => 'Locally bred',
                'sex' => 'mixed',
                'typical_adult_size' => 'Medium (3.5-4.5kg)',
                'vaccination_status' => ['dewormed'],
                'price' => 15000,
                'stock' => 8,
                'supports_inspection' => true,
                'supports_online_purchase' => true,
                'highlights' => ['Dual-purpose breed', 'Hardy and easy to manage', 'Dewormed'],
                'is_featured' => false,
                'order' => 6,
                '_media' => ['cover' => 'Chinchilla Rabbit', 'gallery' => ['Chinchilla rabbit', 'Feeding time']],
            ],
            [
                'name' => 'Dutch Rabbit',
                'slug' => 'dutch-rabbit',
                'listing_type' => 'pool',
                'category' => 'rabbit',
                'breed' => 'Dutch',
                'description' => 'A compact, even-tempered breed that does well as both a pet and a smallholder animal. Sold from a clean, healthy pool.',
                'origin' => 'Locally bred',
                'sex' => 'mixed',
                'typical_adult_size' => 'Small (2-3kg)',
                'vaccination_status' => null,
                'price' => 10000,
                'stock' => 3,
                'supports_inspection' => true,
                'supports_online_purchase' => true,
                'highlights' => ['Compact and even-tempered', 'Good for pet or smallholding', 'Healthy pool'],
                'is_featured' => false,
                'order' => 7,
                '_media' => ['cover' => 'Dutch Rabbit', 'gallery' => ['Dutch rabbit', 'Rabbit run']],
            ],

            // Pool grasscutters
            [
                'name' => 'Grasscutter — Starter Pair',
                'slug' => 'grasscutter-starter-pair',
                'listing_type' => 'pool',
                'category' => 'grasscutter',
                'breed' => 'Greater Cane Rat',
                'description' => 'Young grasscutters ideal for new farmers starting out. Sourced from a disease-free breeding stock and ready for relocation.',
                'origin' => 'Locally bred',
                'sex' => 'mixed',
                'typical_adult_size' => 'Small (1-2kg)',
                'vaccination_status' => null,
                'price' => 35000,
                'stock' => 12,
                'supports_inspection' => true,
                'supports_online_purchase' => true,
                'highlights' => ['Disease-free stock', 'Ideal for beginners', 'Setup guidance included'],
                'is_featured' => true,
                'order' => 8,
                '_media' => ['cover' => 'Grasscutter Starter', 'gallery' => ['Grasscutter pen', 'Young grasscutters']],
            ],
            [
                'name' => 'Grasscutter — Grower Colony',
                'slug' => 'grasscutter-grower-colony',
                'listing_type' => 'pool',
                'category' => 'grasscutter',
                'breed' => 'Greater Cane Rat',
                'description' => 'Mid-stage grasscutters for farmers scaling up production. Healthy, well-fed animals with strong growth rates.',
                'origin' => 'Locally bred',
                'sex' => 'mixed',
                'typical_adult_size' => 'Medium (3-4kg)',
                'vaccination_status' => null,
                'price' => 55000,
                'stock' => 6,
                'supports_inspection' => true,
                'supports_online_purchase' => true,
                'highlights' => ['Strong growth rates', 'Well-fed and healthy', 'Good for scaling up'],
                'is_featured' => false,
                'order' => 9,
                '_media' => ['cover' => 'Grasscutter Grower', 'gallery' => ['Grower colony', 'Grasscutter feeding']],
            ],
            [
                'name' => 'Grasscutter — Breeding Stock',
                'slug' => 'grasscutter-breeding-stock',
                'listing_type' => 'pool',
                'category' => 'grasscutter',
                'breed' => 'Greater Cane Rat',
                'description' => 'Mature, proven grasscutters selected for breeding. Sold to farmers establishing or refreshing their own colonies.',
                'origin' => 'Locally bred',
                'sex' => 'mixed',
                'typical_adult_size' => 'Large (5-7kg)',
                'vaccination_status' => null,
                'price' => 90000,
                'stock' => 4,
                'supports_inspection' => true,
                'supports_online_purchase' => true,
                'highlights' => ['Proven breeding animals', 'Mature and selected', 'Records available on inspection'],
                'is_featured' => false,
                'order' => 10,
                '_media' => ['cover' => 'Grasscutter Breeding', 'gallery' => ['Breeding stock', 'Mature grasscutter']],
            ],
        ];
    }

    /**
     * Generate a placeholder JPG and return its temporary path.
     */
    private function placeholderImage(string $label, string $sub, string $category): string
    {
        $palettes = [
            'dog' => [38, 46, 33],
            'cat' => [46, 38, 44],
            'rabbit' => [33, 44, 46],
            'grasscutter' => [44, 40, 30],
            'other' => [40, 40, 40],
        ];
        $bg = $palettes[$category] ?? $palettes['other'];

        $size = 1000;
        $image = imagecreatetruecolor($size, $size);

        $base = imagecolorallocate($image, $bg[0], $bg[1], $bg[2]);
        imagefilledrectangle($image, 0, 0, $size, $size, $base);

        $lighten = imagecolorallocate($image, min(255, $bg[0] + 26), min(255, $bg[1] + 26), min(255, $bg[2] + 26));
        for ($i = 0; $i < 12; $i++) {
            imagefilledpolygon($image, [
                $size - ($i * 150), 0,
                $size - ($i * 150) + 70, 0,
                $size - ($i * 150) - 240, $size,
                $size - ($i * 150) - 310, $size,
            ], $lighten);
        }

        $shade = imagecolorallocatealpha($image, 16, 14, 12, 28);
        imagefilledrectangle($image, 0, $size - 230, $size, $size, $shade);

        $orange = imagecolorallocate($image, 245, 130, 32);
        imagefilledrectangle($image, 70, $size - 178, 160, $size - 164, $orange);

        $white = imagecolorallocate($image, 255, 255, 255);
        $cream = imagecolorallocate($image, 232, 224, 216);
        imagestring($image, 5, 70, $size - 150, $label, $white);
        imagestring($image, 4, 70, $size - 118, $sub, $cream);
        imagestring($image, 2, 70, 48, 'BLOMFREE KENNEL & FARM  -  PLACEHOLDER IMAGE', $orange);

        $path = storage_path('app/seed-animal-'.Str::random(12).'.jpg');
        imagejpeg($image, $path, 85);
        imagedestroy($image);

        return $path;
    }
}
