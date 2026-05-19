<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Share2, Wallet, ShieldCheck, TrendingUp } from 'lucide-vue-next';
import AppLayout from '@/Layouts/AppLayout.vue';
import Section from '@/Components/Section.vue';
import Button from '@/Components/Button.vue';
import SeoMeta from '@/Components/SeoMeta.vue';
import { formatNaira } from '@/lib/format';

defineProps<{
    commissionHoldDays: number;
    minimumWithdrawal: number;
}>();
</script>

<template>
    <Head title="Become a BLOMFREE Affiliate" />
    <SeoMeta
        title="Become a BLOMFREE Affiliate"
        description="Share your unique code, earn fixed commissions on Collections, Gadgets and Kennel & Farm sales. Withdraw to your Nigerian bank account."
    />

    <AppLayout>
        <Section width="narrow">
            <h1 class="text-4xl font-extrabold tracking-tight sm:text-5xl">
                Get paid to share BLOMFREE
            </h1>
            <p class="mt-4 text-lg text-brand-dark/70">
                Join our affiliate program. Share your unique code with friends,
                family and your audience. Earn a fixed commission on every
                eligible product, gadget or animal they buy.
            </p>

            <div class="mt-8 flex flex-wrap gap-3">
                <Button href="/affiliate/signup" size="lg">Create your code</Button>
                <Button href="/affiliate/terms" variant="outline" size="lg">Read the terms</Button>
            </div>

            <div class="mt-12 grid gap-6 sm:grid-cols-2">
                <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-black/5">
                    <Share2 class="h-6 w-6 text-brand-orange" />
                    <h3 class="mt-3 text-lg font-bold">Manual code, no links</h3>
                    <p class="mt-1 text-sm text-brand-dark/70">
                        We don't use referral links or cookies. Just share your
                        code — buyers enter it at checkout.
                    </p>
                </div>
                <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-black/5">
                    <Wallet class="h-6 w-6 text-brand-orange" />
                    <h3 class="mt-3 text-lg font-bold">Fixed commission per sale</h3>
                    <p class="mt-1 text-sm text-brand-dark/70">
                        Every eligible item shows you exactly what you'll earn.
                        Withdraw to your Nigerian bank — minimum {{ formatNaira(minimumWithdrawal) }}.
                    </p>
                </div>
                <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-black/5">
                    <ShieldCheck class="h-6 w-6 text-brand-orange" />
                    <h3 class="mt-3 text-lg font-bold">Protected payouts</h3>
                    <p class="mt-1 text-sm text-brand-dark/70">
                        Commissions clear {{ commissionHoldDays }} days after delivery to cover refunds.
                        After that, the money is yours.
                    </p>
                </div>
                <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-black/5">
                    <TrendingUp class="h-6 w-6 text-brand-orange" />
                    <h3 class="mt-3 text-lg font-bold">Across all subsidiaries</h3>
                    <p class="mt-1 text-sm text-brand-dark/70">
                        Earn on Collections, Gadgets and Kennel &amp; Farm. Lands are not eligible.
                    </p>
                </div>
            </div>

            <div class="mt-10 rounded-2xl bg-brand-dark/5 p-6 text-sm text-brand-dark/80">
                Already an affiliate?
                <Link href="/login" class="font-semibold text-brand-orange hover:underline">Log in to your dashboard</Link>.
            </div>
        </Section>
    </AppLayout>
</template>
