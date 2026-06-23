<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    LayoutDashboard,
    Users,
    ShieldCheck,
    LogOut,
    ArrowRight,
    Wallet,
} from 'lucide-vue-next';
import AppLayout from '@/Layouts/AppLayout.vue';
import Section from '@/Components/Section.vue';
import Badge from '@/Components/Badge.vue';
import SeoMeta from '@/Components/SeoMeta.vue';

interface Tile {
    title: string;
    description: string;
    href: string;
    icon: typeof LayoutDashboard;
    accent?: boolean;
}

const props = defineProps<{
    user: { name: string; email: string };
    isAdmin: boolean;
    isAffiliate: boolean;
    installmentCount: number;
}>();

const tiles = computeTiles();

function computeTiles(): Tile[] {
    const out: Tile[] = [];

    out.push({
        title: 'My Account',
        description: props.installmentCount
            ? `${props.installmentCount} installment plan${props.installmentCount === 1 ? '' : 's'} — track payments, download receipts.`
            : 'Track installments, manage your profile and bank details.',
        href: '/account',
        icon: LayoutDashboard,
        accent: true,
    });

    if (props.isAffiliate) {
        out.push({
            title: 'Affiliate Dashboard',
            description: 'Your commissions, withdrawals and referral activity.',
            href: '/affiliate/dashboard',
            icon: Users,
        });
    } else {
        out.push({
            title: 'Become an Affiliate',
            description: 'Earn commission on every referral. Apply in a minute.',
            href: '/affiliate',
            icon: Wallet,
        });
    }

    if (props.isAdmin) {
        out.push({
            title: 'Admin Panel',
            description: 'Manage products, orders, installments and settings.',
            href: '/admin',
            icon: ShieldCheck,
        });
    }

    return out;
}

const logout = (): void => {
    router.post('/logout');
};
</script>

<template>
    <Head title="My Hub" />
    <SeoMeta title="My BLOMFREE Hub" />

    <AppLayout>
        <Section width="narrow">
            <Badge variant="info">Signed in</Badge>
            <h1 class="mt-3 text-3xl font-extrabold tracking-tight sm:text-4xl">
                Welcome back, {{ user.name }}
            </h1>
            <p class="mt-2 text-brand-dark/70">
                Where would you like to go?
            </p>

            <div class="mt-8 grid gap-4 sm:grid-cols-2">
                <Link
                    v-for="tile in tiles"
                    :key="tile.href"
                    :href="tile.href"
                    class="group flex flex-col gap-3 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-black/5 transition-shadow hover:shadow-md"
                    :class="
                        tile.accent
                            ? 'ring-brand-orange/30 bg-brand-orange/5'
                            : ''
                    "
                >
                    <span
                        class="inline-flex h-11 w-11 items-center justify-center rounded-xl bg-brand-orange/10 text-brand-orange"
                    >
                        <component :is="tile.icon" class="h-5 w-5" />
                    </span>
                    <div class="flex-1">
                        <h2 class="text-lg font-bold tracking-tight">{{ tile.title }}</h2>
                        <p class="mt-1 text-sm text-brand-dark/70">{{ tile.description }}</p>
                    </div>
                    <span
                        class="inline-flex items-center gap-1.5 text-sm font-semibold text-brand-orange"
                    >
                        Open
                        <ArrowRight
                            class="h-4 w-4 transition-transform duration-300 group-hover:translate-x-1"
                        />
                    </span>
                </Link>
            </div>

            <div
                class="mt-10 flex items-center justify-between rounded-2xl bg-brand-dark/5 p-4 text-sm"
            >
                <div>
                    Signed in as
                    <span class="font-semibold">{{ user.email }}</span>
                </div>
                <button
                    type="button"
                    class="inline-flex items-center gap-1.5 font-semibold text-brand-dark/70 transition-colors hover:text-brand-dark"
                    @click="logout"
                >
                    <LogOut class="h-4 w-4" />
                    Log out
                </button>
            </div>
        </Section>
    </AppLayout>
</template>
