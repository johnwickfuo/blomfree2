<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { LayoutDashboard, ShoppingBag, Wallet, CreditCard, Landmark, UserCog, LogOut } from 'lucide-vue-next';
import AppLayout from '@/Layouts/AppLayout.vue';

interface NavItem {
    label: string;
    href: string;
    icon: typeof LayoutDashboard;
}

const nav: NavItem[] = [
    { label: 'Overview', href: '/affiliate/dashboard', icon: LayoutDashboard },
    { label: 'Orders', href: '/affiliate/dashboard/orders', icon: ShoppingBag },
    { label: 'Commissions', href: '/affiliate/dashboard/commissions', icon: Wallet },
    { label: 'Withdrawals', href: '/affiliate/dashboard/withdrawals', icon: CreditCard },
    { label: 'Bank details', href: '/affiliate/dashboard/bank-details', icon: Landmark },
    { label: 'Profile', href: '/affiliate/dashboard/profile', icon: UserCog },
];

const logout = (): void => {
    router.post('/logout');
};
</script>

<template>
    <AppLayout>
        <div class="mx-auto w-full max-w-screen-2xl px-4 py-10 sm:px-6 lg:px-8">
            <div class="grid gap-8 lg:grid-cols-[240px,1fr]">
                <aside class="space-y-1">
                    <p class="px-3 pb-2 text-xs font-bold uppercase tracking-wider text-brand-dark/50">
                        Affiliate
                    </p>
                    <Link
                        v-for="item in nav"
                        :key="item.href"
                        :href="item.href"
                        class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-semibold text-brand-dark/70 transition-colors hover:bg-brand-orange/10 hover:text-brand-orange"
                        :class="$page.url === item.href ? 'bg-brand-orange/10 text-brand-orange' : ''"
                    >
                        <component :is="item.icon" class="h-4 w-4" />
                        {{ item.label }}
                    </Link>
                    <button
                        type="button"
                        @click="logout"
                        class="mt-4 flex w-full items-center gap-2 rounded-lg px-3 py-2 text-sm font-semibold text-brand-dark/70 transition-colors hover:bg-red-50 hover:text-red-700"
                    >
                        <LogOut class="h-4 w-4" />
                        Log out
                    </button>
                </aside>

                <main>
                    <slot />
                </main>
            </div>
        </div>
    </AppLayout>
</template>
