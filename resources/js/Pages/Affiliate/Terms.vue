<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Section from '@/Components/Section.vue';
import SeoMeta from '@/Components/SeoMeta.vue';
import { formatNaira } from '@/lib/format';

defineProps<{
    commissionHoldDays: number;
    fallbackDays: number;
    minimumWithdrawal: number;
    withdrawalFee: number;
}>();
</script>

<template>
    <Head title="Affiliate Terms — BLOMFREE" />
    <SeoMeta title="Affiliate Terms" description="The rules of the BLOMFREE Affiliate Program." />

    <AppLayout>
        <Section width="narrow">
            <h1 class="text-3xl font-extrabold tracking-tight sm:text-4xl">
                Affiliate Program Terms
            </h1>

            <div class="prose prose-sm mt-8 max-w-none text-brand-dark/80">
                <h2>1. Eligibility</h2>
                <p>You must be 18+, resident in Nigeria, with a valid Nigerian bank account in your own name. One affiliate account per person.</p>

                <h2>2. Your code</h2>
                <p>Each affiliate gets one unique code. Buyers enter it at checkout — there are no tracking links or cookies. Only orders where the code is correctly entered will earn commissions.</p>

                <h2>3. What's eligible</h2>
                <p>Items in <strong>BLOMFREE Collections</strong>, <strong>BLOMFREE Gadgets</strong> and <strong>BLOMFREE Kennel &amp; Farm</strong> that have an affiliate price configured. Land sales are <strong>not eligible</strong>.</p>

                <h2>4. Commission lifecycle</h2>
                <ul>
                    <li><strong>Pending</strong>: created when the order is paid.</li>
                    <li><strong>Available</strong>: once the order is delivered, plus a {{ commissionHoldDays }}-day hold to allow for refunds. If we don't confirm delivery, the commission auto-releases after {{ fallbackDays }} days.</li>
                    <li><strong>Reversed</strong>: if the order is cancelled or refunded, the commission is reversed.</li>
                    <li><strong>Withdrawn</strong>: when we pay out the matching withdrawal.</li>
                </ul>

                <h2>5. Withdrawals</h2>
                <p>Minimum withdrawal is {{ formatNaira(minimumWithdrawal) }}. We charge a flat transfer fee of {{ formatNaira(withdrawalFee) }} per withdrawal. Payouts are made within 1–3 business days. You may only have one withdrawal request pending at a time.</p>

                <h2>6. Self-referrals</h2>
                <p>You cannot use your own code on your own orders. The system blocks this automatically.</p>

                <h2>7. Prohibited promotion</h2>
                <p>No spam, no impersonation of BLOMFREE staff, no misleading claims about products, no paid search ads on BLOMFREE-branded keywords. Breaching these rules may result in suspension and forfeiture of any unpaid commissions.</p>

                <h2>8. Suspension</h2>
                <p>We may suspend an affiliate account for fraud, abuse or breach of these terms. Existing balances may be withheld pending investigation.</p>

                <h2>9. Changes</h2>
                <p>We may update these terms at any time. Continued use of the program means you accept the latest version.</p>
            </div>
        </Section>
    </AppLayout>
</template>
