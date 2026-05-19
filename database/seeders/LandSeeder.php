<?php

namespace Database\Seeders;

use App\Models\Land;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class LandSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->lands() as $index => $data) {
            $media = $data['_media'];
            unset($data['_media']);

            $land = Land::create($data);

            $land->addMedia($this->placeholderImage($media['cover'], $data['state'], [34, 49, 38]))
                ->toMediaCollection('cover');

            foreach ($media['gallery'] as $caption) {
                $palette = [[46, 40, 33], [30, 42, 52]][array_rand([0, 1])];
                $land->addMedia($this->placeholderImage($caption, $data['city_or_lga'], $palette))
                    ->toMediaCollection('gallery');
            }
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function lands(): array
    {
        return [
            [
                'title' => 'Greenfield Estate, Yenagoa',
                'slug' => 'greenfield-estate-yenagoa',
                'location_address' => 'Off Mbiama-Yenagoa Road, Akenfa, Yenagoa',
                'state' => 'Bayelsa',
                'city_or_lga' => 'Yenagoa',
                'number_of_plots' => 12,
                'plot_size_sqm' => 648,
                'price_per_plot' => 2500000,
                'price_label' => 'Negotiable',
                'description' => 'A serene residential estate on the outskirts of Yenagoa, fully sand-filled and dry all year round. Greenfield Estate is laid out for comfortable family living with wide internal roads and a gated perimeter.',
                'features' => ['Gated estate', 'Sand-filled plots', 'Perimeter fencing', 'Wide internal roads', '24/7 security'],
                'close_to_landmarks' => ['Federal University Otuoke road', 'Yenagoa city centre', 'NDU Permanent Site'],
                'has_good_access_road' => true,
                'is_flood_free' => true,
                'installment_available' => true,
                'document_status' => ['c_of_o', 'survey_plan', 'deed_of_assignment'],
                'status' => 'available',
                'is_featured' => true,
                'order' => 1,
                '_media' => [
                    'cover' => 'Greenfield Estate',
                    'gallery' => ['Estate entrance', 'Internal road'],
                ],
            ],
            [
                'title' => 'Pearl Gardens, Port Harcourt',
                'slug' => 'pearl-gardens-port-harcourt',
                'location_address' => 'Off Airport Road, Igwuruta, Port Harcourt',
                'state' => 'Rivers',
                'city_or_lga' => 'Port Harcourt',
                'number_of_plots' => 6,
                'plot_size_sqm' => 648,
                'price_per_plot' => 4000000,
                'price_label' => 'Starting from',
                'description' => 'Premium plots in a fast-developing corridor near the Port Harcourt International Airport. Pearl Gardens sits on high, firm ground with excellent drainage and a tarred approach road.',
                'features' => ['Tarred access road', 'Good drainage', 'Dry firm land', 'Close to airport'],
                'close_to_landmarks' => ['Port Harcourt International Airport', 'Igwuruta market'],
                'has_good_access_road' => true,
                'is_flood_free' => true,
                'installment_available' => false,
                'document_status' => ['governors_consent', 'survey_plan', 'receipt_of_purchase'],
                'status' => 'available',
                'is_featured' => true,
                'order' => 2,
                '_media' => [
                    'cover' => 'Pearl Gardens',
                    'gallery' => ['Approach road', 'Cleared plots'],
                ],
            ],
            [
                'title' => 'Warri Heights Layout',
                'slug' => 'warri-heights-layout',
                'location_address' => 'Effurun-Sapele Road, Warri',
                'state' => 'Delta',
                'city_or_lga' => 'Warri',
                'number_of_plots' => 20,
                'plot_size_sqm' => 648,
                'price_per_plot' => 1800000,
                'price_label' => null,
                'description' => 'An affordable layout ideal for first-time buyers and investors. Warri Heights offers a large block of contiguous plots with flexible payment options.',
                'features' => ['Affordable entry price', 'Large contiguous block', 'Investor friendly', 'Flexible payment'],
                'close_to_landmarks' => ['Effurun roundabout', 'Delta Mall'],
                'has_good_access_road' => true,
                'is_flood_free' => true,
                'installment_available' => true,
                'document_status' => ['survey_plan', 'receipt_of_purchase'],
                'status' => 'available',
                'is_featured' => false,
                'order' => 3,
                '_media' => [
                    'cover' => 'Warri Heights',
                    'gallery' => ['Layout signpost', 'Open field'],
                ],
            ],
            [
                'title' => 'Lekki Phase 3 Annex',
                'slug' => 'lekki-phase-3-annex',
                'location_address' => 'Abijo GRA, Lekki-Epe Expressway, Lagos',
                'state' => 'Lagos',
                'city_or_lga' => 'Eti-Osa',
                'number_of_plots' => 4,
                'plot_size_sqm' => 648,
                'price_per_plot' => 18000000,
                'price_label' => 'Negotiable',
                'description' => 'High-value plots within the Abijo GRA corridor, one of the most sought-after stretches of the Lekki-Epe Expressway. Excellent for luxury residential development.',
                'features' => ['Prime Lekki corridor', 'Excellent appreciation', 'Luxury neighbourhood', 'Close to Dangote Refinery'],
                'close_to_landmarks' => ['Abijo GRA', 'Novare Lekki Mall', 'Dangote Refinery'],
                'has_good_access_road' => true,
                'is_flood_free' => true,
                'installment_available' => false,
                'document_status' => ['c_of_o', 'governors_consent', 'deed_of_assignment', 'survey_plan'],
                'status' => 'reserved',
                'is_featured' => true,
                'order' => 4,
                '_media' => [
                    'cover' => 'Lekki Phase 3 Annex',
                    'gallery' => ['Expressway frontage', 'Surveyed plots'],
                ],
            ],
            [
                'title' => 'Gwarinpa Extension Plots',
                'slug' => 'gwarinpa-extension-plots',
                'location_address' => 'Gwarinpa Extension, Life Camp axis, Abuja',
                'state' => 'FCT',
                'city_or_lga' => 'Abuja Municipal',
                'number_of_plots' => 8,
                'plot_size_sqm' => 648,
                'price_per_plot' => 9500000,
                'price_label' => 'Starting from',
                'description' => 'Well-positioned plots within the Gwarinpa Extension, minutes from Life Camp and the Abuja city centre. Suitable for residential and mixed-use development.',
                'features' => ['City-centre access', 'Mixed-use friendly', 'Power infrastructure nearby', 'Gated layout'],
                'close_to_landmarks' => ['Life Camp', 'Gwarinpa Estate', 'Kubwa Expressway'],
                'has_good_access_road' => true,
                'is_flood_free' => true,
                'installment_available' => true,
                'document_status' => ['c_of_o', 'survey_plan'],
                'status' => 'available',
                'is_featured' => false,
                'order' => 5,
                '_media' => [
                    'cover' => 'Gwarinpa Extension',
                    'gallery' => ['Estate gate', 'Graded road'],
                ],
            ],
            [
                'title' => 'Benin Ring Road Estate',
                'slug' => 'benin-ring-road-estate',
                'location_address' => 'Off Sapele Road, Oredo, Benin City',
                'state' => 'Edo',
                'city_or_lga' => 'Oredo',
                'number_of_plots' => 10,
                'plot_size_sqm' => 648,
                'price_per_plot' => 3200000,
                'price_label' => null,
                'description' => 'A completed and fully subscribed estate off Sapele Road. Listed here as a reference of the BLOMFREE Estates standard.',
                'features' => ['Fully developed', 'Established neighbourhood', 'Tarred roads', 'Street lighting'],
                'close_to_landmarks' => ['Benin Ring Road', 'Sapele Road junction'],
                'has_good_access_road' => true,
                'is_flood_free' => true,
                'installment_available' => false,
                'document_status' => ['c_of_o', 'governors_consent', 'deed_of_assignment', 'survey_plan', 'receipt_of_purchase'],
                'status' => 'sold',
                'is_featured' => false,
                'order' => 6,
                '_media' => [
                    'cover' => 'Benin Ring Road Estate',
                    'gallery' => ['Estate street', 'Completed homes'],
                ],
            ],
        ];
    }

    /**
     * Generate a placeholder JPG and return its temporary path.
     *
     * @param  array{0:int,1:int,2:int}  $bg
     */
    private function placeholderImage(string $label, string $sub, array $bg): string
    {
        $width = 1200;
        $height = 800;
        $image = imagecreatetruecolor($width, $height);

        $base = imagecolorallocate($image, $bg[0], $bg[1], $bg[2]);
        imagefilledrectangle($image, 0, 0, $width, $height, $base);

        $lighten = imagecolorallocate($image, min(255, $bg[0] + 22), min(255, $bg[1] + 22), min(255, $bg[2] + 22));
        for ($i = 0; $i < 14; $i++) {
            imagefilledpolygon($image, [
                $width - ($i * 120), 0,
                $width - ($i * 120) + 60, 0,
                $width - ($i * 120) - 200, $height,
                $width - ($i * 120) - 260, $height,
            ], $lighten);
        }

        $shade = imagecolorallocatealpha($image, 18, 16, 14, 30);
        imagefilledrectangle($image, 0, $height - 240, $width, $height, $shade);

        $orange = imagecolorallocate($image, 245, 130, 32);
        imagefilledrectangle($image, 60, $height - 190, 150, $height - 176, $orange);

        $white = imagecolorallocate($image, 255, 255, 255);
        $cream = imagecolorallocate($image, 232, 224, 216);
        imagestring($image, 5, 60, $height - 160, $label, $white);
        imagestring($image, 4, 60, $height - 128, $sub, $cream);
        imagestring($image, 2, 60, 44, 'BLOMFREE ESTATES & PROPERTIES  -  PLACEHOLDER IMAGE', $orange);

        $path = storage_path('app/seed-land-'.Str::random(12).'.jpg');
        imagejpeg($image, $path, 85);
        imagedestroy($image);

        return $path;
    }
}
