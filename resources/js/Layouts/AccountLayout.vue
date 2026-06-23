<script setup lang="ts">
import { computed } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import {
    LayoutDashboard,
    Calendar,
    ShoppingBag,
    Landmark,
    UserCog,
    LogOut,
    Wallet,
    CreditCard,
    Sparkles,
    Users,
} from 'lucide-vue-next';
import AppLayout from '@/Layouts/AppLayout.vue';

interface NavItem {
    label: string;
    href: string;
    icon: typeof LayoutDashboard;
}

const accountNav: NavItem[] = [
    { label: 'Overview', href: '/account', icon: LayoutDashboard },
    { label: 'My Installments', href: '/account/installments', icon: Calendar },
    { label: 'Order history', href: '/account/orders', icon: ShoppingBag },
    { label: 'Bank details', href: '/account/bank', icon: Landmark },
    { label: 'Profile & security', href: '/account/profile', icon: UserCog },
];

const affiliateNav: NavItem[] = [
    { label: 'Overview', href: '/affiliate/dashboard', icon: LayoutDashboard },
    { label: 'Referred orders', href: '/affiliate/dashboard/orders', icon: ShoppingBag },
    { label: 'Commissions', href: '/affiliate/dashboard/commissions', icon: Wallet },
    { label: 'Withdrawals', href: '/affiliate/dashboard/withdrawals', icon: CreditCard },
];

const page = usePage();
const isAffiliate = computed(
    () => (page.props.auth as { is_affiliate?: boolean } | undefined)?.is_affiliate === true,
);

const logout = (): void => {
    router.post('/logout');
};

const isActive = (href: string): boolean =>
    page.url === href || page.url.startsWith(href + '/');
</script>

<template>
    <AppLayout>
        <div class="mx-auto w-full max-w-screen-2xl px-4 py-10 sm:px-6 lg:px-8">
            <div class="grid gap-8 lg:grid-cols-[240px,1fr]">
                <aside class="space-y-6">
                    <div class="space-y-1">
                        <p
                            class="px-3 pb-2 text-xs font-bold uppercase tracking-wider text-brand-dark/50"
                        >
                            My Account
                        </p>
                        <Link
                            v-for="item in accountNav"
                            :key="item.href"
                            :href="item.href"
                            class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-semibold text-brand-dark/70 transition-colors hover:bg-brand-orange/10 hover:text-brand-orange"
                            :class="
                                isActive(item.href)
                                    ? 'bg-brand-orange/10 text-brand-orange'
                                    : ''
                            "
                        >
                            <component :is="item.icon" class="h-4 w-4" />
                            {{ item.label }}
                        </Link>
                    </div>

                    <div v-if="isAffiliate" class="space-y-1">
                        <p
                            class="px-3 pb-2 text-xs font-bold uppercase tracking-wider text-brand-orange"
                        >
                            Affiliate Program
                        </p>
                        <Link
                            v-for="item in affiliateNav"
                            :key="item.href"
                            :href="item.href"
                            class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-semibold text-brand-dark/70 transition-colors hover:bg-brand-orange/10 hover:text-brand-orange"
                            :class="
                                isActive(item.href)
                                    ? 'bg-brand-orange/10 text-brand-orange'
                                    : ''
                            "
                        >
                            <component :is="item.icon" class="h-4 w-4" />
                            {{ item.label }}
                        </Link>
                    </div>

                    <div v-else class="space-y-1">
                        <p
                            class="px-3 pb-2 text-xs font-bold uppercase tracking-wider text-brand-dark/50"
                        >
                            Earn with us
                        </p>
                        <Link
                            href="/account/affiliate/become"
                            class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-semibold text-brand-dark/70 transition-colors hover:bg-brand-orange/10 hover:text-brand-orange"
                            :class="
                                isActive('/account/affiliate/become')
                                    ? 'bg-brand-orange/10 text-brand-orange'
                                    : ''
                            "
                        >
                            <Sparkles class="h-4 w-4" />
                            Become an Affiliate
                        </Link>
                    </div>

                    <button
                        type="button"
                        @click="logout"
                        class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-sm font-semibold text-brand-dark/70 transition-colors hover:bg-red-50 hover:text-red-700"
                    >
                        <LogOut class="h-4 w-4" />
                        Log out
                    </button>
                </aside>

                <main><slot /></main>
            </div>
        </div>
    </AppLayout>
</template>
