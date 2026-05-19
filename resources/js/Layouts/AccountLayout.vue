<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { LayoutDashboard, Calendar, ShoppingBag, Landmark, UserCog, LogOut } from 'lucide-vue-next';
import AppLayout from '@/Layouts/AppLayout.vue';

interface NavItem { label: string; href: string; icon: typeof LayoutDashboard }

const nav: NavItem[] = [
    { label: 'Overview', href: '/account', icon: LayoutDashboard },
    { label: 'My Installments', href: '/account/installments', icon: Calendar },
    { label: 'Order history', href: '/account/orders', icon: ShoppingBag },
    { label: 'Bank details', href: '/account/bank', icon: Landmark },
    { label: 'Profile & security', href: '/account/profile', icon: UserCog },
];

const logout = (): void => { router.post('/logout'); };
</script>

<template>
    <AppLayout>
        <div class="mx-auto w-full max-w-screen-2xl px-4 py-10 sm:px-6 lg:px-8">
            <div class="grid gap-8 lg:grid-cols-[240px,1fr]">
                <aside class="space-y-1">
                    <p class="px-3 pb-2 text-xs font-bold uppercase tracking-wider text-brand-dark/50">My Account</p>
                    <Link
                        v-for="item in nav"
                        :key="item.href"
                        :href="item.href"
                        class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-semibold text-brand-dark/70 transition-colors hover:bg-brand-orange/10 hover:text-brand-orange"
                        :class="$page.url === item.href || $page.url.startsWith(item.href + '/') ? 'bg-brand-orange/10 text-brand-orange' : ''"
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

                <main><slot /></main>
            </div>
        </div>
    </AppLayout>
</template>
