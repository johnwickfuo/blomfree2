<?php

namespace App\Support;

/**
 * Canonical list of Nigerian states (including the FCT) used in the
 * shipping-zone admin form and the public checkout state dropdown.
 */
class NigerianStates
{
    /**
     * @var list<string>
     */
    public const ALL = [
        'Abia',
        'Adamawa',
        'Akwa Ibom',
        'Anambra',
        'Bauchi',
        'Bayelsa',
        'Benue',
        'Borno',
        'Cross River',
        'Delta',
        'Ebonyi',
        'Edo',
        'Ekiti',
        'Enugu',
        'FCT',
        'Gombe',
        'Imo',
        'Jigawa',
        'Kaduna',
        'Kano',
        'Katsina',
        'Kebbi',
        'Kogi',
        'Kwara',
        'Lagos',
        'Nasarawa',
        'Niger',
        'Ogun',
        'Ondo',
        'Osun',
        'Oyo',
        'Plateau',
        'Rivers',
        'Sokoto',
        'Taraba',
        'Yobe',
        'Zamfara',
    ];

    /**
     * @return list<string>
     */
    public static function all(): array
    {
        return self::ALL;
    }

    /**
     * Convenient `[state => state]` map for Filament Select options.
     *
     * @return array<string, string>
     */
    public static function forSelect(): array
    {
        return array_combine(self::ALL, self::ALL);
    }
}
