import type { Component } from 'vue';
import { MapPin, PawPrint, Shirt, Smartphone } from 'lucide-vue-next';

export interface Subsidiary {
    name: string;
    tagline: string;
    href: string;
    icon: Component;
}

export const subsidiaries: Subsidiary[] = [
    {
        name: 'BLOMFREE Estates & Properties',
        tagline: 'Premium lands at flood-free locations across Nigeria',
        href: '/lands',
        icon: MapPin,
    },
    {
        name: 'BLOMFREE Kennel & Farm',
        tagline: 'Imported and locally bred quality animals',
        href: '/kennel-farm',
        icon: PawPrint,
    },
    {
        name: 'BLOMFREE Collections',
        tagline: 'Unisex clothing and accessories',
        href: '/collections',
        icon: Shirt,
    },
    {
        name: 'BLOMFREE Gadgets & Accessories',
        tagline: 'Phones, consoles, audio, and smart devices',
        href: '/gadgets',
        icon: Smartphone,
    },
];
