<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Section from '@/Components/Section.vue';
import SeoMeta from '@/Components/SeoMeta.vue';

defineProps<{
    termsVersion: string;
    forfeiturePercentage: number;
    landResaleWaitDays: number;
}>();
</script>

<template>
    <Head title="Installment Terms — BLOMFREE" />
    <SeoMeta title="Installment Terms" description="The rules of the BLOMFREE Installment Program." />

    <AppLayout>
        <Section width="narrow">
            <h1 class="text-3xl font-extrabold tracking-tight sm:text-4xl">BLOMFREE Installment Terms</h1>
            <p class="mt-1 text-sm text-brand-dark/60">Version {{ termsVersion }}</p>

            <div class="prose prose-sm mt-8 max-w-none text-brand-dark/80">
                <h2>1. How it works</h2>
                <p>An installment plan lets you pay for an eligible item over time, instead of all at once. You commit to the total price, pay an initial down payment to activate the plan, then continue paying any amount you choose until your overall deadline.</p>

                <h2>2. Eligibility</h2>
                <p>Only items in <strong>BLOMFREE Estates &amp; Properties</strong> (lands) and <strong>BLOMFREE Gadgets &amp; Accessories</strong> can be purchased on installment. Collections (clothing) and Kennel &amp; Farm (animals) are <strong>not eligible</strong>.</p>

                <h2>3. Down payment</h2>
                <p>Each item sets its own minimum down payment percentage (typically 25–40%). Your plan does not activate until your cumulative payments reach this minimum.</p>

                <h2>4. Deadline</h2>
                <p>Your deadline is computed as activation date plus the item's maximum length (1–36 months). Suggested intermediate payment dates are guidelines — only the overall deadline matters. Payments can be made at any time.</p>

                <h2>5. Forfeiture clause</h2>
                <p>If you fail to complete payment by the deadline, or if you voluntarily cancel an active plan, <strong>{{ forfeiturePercentage }}% of what you have paid is forfeited as a non-refundable fee.</strong> The remaining {{ 100 - forfeiturePercentage }}% will be refunded to your bank account on file.</p>

                <h2>6. Lands vs. Gadgets</h2>
                <ul>
                    <li><strong>Lands:</strong> approved plans reserve the plot. If you default, the plot returns to public listings. We process your 90% refund once the plot is resold, or after {{ landResaleWaitDays }} days at admin discretion.</li>
                    <li><strong>Gadgets:</strong> stock is not reserved during your plan. If, on completion, the item is unavailable, you may choose to wait for restock or receive a full refund (no forfeiture). On default, the 90% refund is processed immediately.</li>
                </ul>

                <h2>7. Early completion</h2>
                <p>You can pay off the remaining balance any time without penalty.</p>

                <h2>8. Refund processing</h2>
                <p>Refunds are processed by manual bank transfer to your bank account on file and take 7–14 business days. Please keep your bank details up to date in your account.</p>

                <h2>9. Affiliate codes</h2>
                <p>An affiliate code applied at plan creation locks in the affiliate price. The associated commission is held in escrow and only paid to the affiliate when the plan completes (full payment received). Defaulted or cancelled plans do not earn commission.</p>

                <h2>10. Suspension</h2>
                <p>BLOMFREE may suspend the installment program in part or in whole at any time. Existing active plans are honoured.</p>

                <h2>11. Disputes</h2>
                <p>Contact <a href="mailto:admin@blomfree.com">admin@blomfree.com</a> for disputes or clarification.</p>
            </div>
        </Section>
    </AppLayout>
</template>
