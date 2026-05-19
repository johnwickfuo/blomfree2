<script setup lang="ts">
import { computed, onMounted } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import {
    Send,
    Phone,
    Mail as MailIcon,
    Instagram,
    Facebook,
    CheckCircle2,
    Sparkles,
} from 'lucide-vue-next';
import AppLayout from '@/Layouts/AppLayout.vue';
import Hero from '@/Components/Hero.vue';
import Section from '@/Components/Section.vue';
import Badge from '@/Components/Badge.vue';
import Button from '@/Components/Button.vue';
import SeoMeta from '@/Components/SeoMeta.vue';
import { formatNigerianPhone } from '@/lib/format';

defineProps<{
    subjects: Record<string, string>;
    whatsappNumber: string;
}>();

const page = usePage();
const flashSuccess = computed(() => page.props.flash?.success ?? null);

const form = useForm({
    name: '',
    email: '',
    phone: '',
    subject: 'general',
    message: '',
    related_url: '',
    website: '', // honeypot — must stay empty
});

const phones = [
    { display: '0810 396 5317', tel: '+2348103965317', wa: '2348103965317' },
    { display: '0813 499 1052', tel: '+2348134991052', wa: '2348134991052' },
];

const faqs = [
    {
        q: 'How do I book a land inspection?',
        a: 'Open any plot on the Lands page and click "Book Inspection". Our team confirms every request within 24 hours and emails you the meeting details.',
    },
    {
        q: 'Do you deliver outside Bayelsa?',
        a: 'Yes — we ship across Nigeria. Shipping is calculated by zone at checkout. If your state is not in our zones, message us on WhatsApp for a custom delivery quote.',
    },
    {
        q: 'Are the animals you sell vaccinated and vet-checked?',
        a: 'Every individual animal lists its vaccination status on the detail page. Pool animals (rabbits, grasscutters) are dewormed and vet-checked before sale.',
    },
    {
        q: 'Can I pay for land in instalments?',
        a: 'Several of our plots support instalments — filter by "Installment Available" on the Lands page or ask us about a plan that works for you.',
    },
    {
        q: 'How are live animals delivered?',
        a: 'Live animals cannot ship via regular courier. After your order, our team contacts you within 24 hours to arrange safe delivery logistics.',
    },
    {
        q: 'Are the gadgets you sell genuine and warranted?',
        a: 'Yes — every phone, console, audio product and accessory is genuine and comes with a manufacturer warranty.',
    },
    {
        q: 'Can I return a product if I change my mind?',
        a: 'Returns are handled case by case. Reach out via this form or WhatsApp within 7 days of delivery and we will sort it.',
    },
    {
        q: 'Do you partner with other businesses?',
        a: 'Yes — for wholesale, partnerships or press, pick the relevant subject above or send us a WhatsApp message.',
    },
];

onMounted(() => {
    // Capture an internal referrer so admins know which page the inquiry came
    // from. External referrers are ignored to avoid leaking third-party URLs.
    if (
        document.referrer &&
        document.referrer.startsWith(window.location.origin) &&
        !document.referrer.endsWith('/contact')
    ) {
        form.related_url = document.referrer;
    }
});

function onPhoneInput(event: Event): void {
    form.phone = formatNigerianPhone((event.target as HTMLInputElement).value);
}

function submit(): void {
    form.post('/inquiries', {
        preserveScroll: true,
        onSuccess: () => form.reset('name', 'email', 'phone', 'message'),
    });
}
</script>

<template>
    <SeoMeta
        title="Contact BLOMFREE & CO."
        description="Reach the BLOMFREE & CO. team — WhatsApp, phone, email, or the contact form. We reply within 24 hours."
    />

    <AppLayout>
        <Hero :overlay="false" align="center" min-height-class="min-h-[44vh]">
            <template #overlay>
                <div
                    class="absolute inset-0"
                    style="
                        background: radial-gradient(
                            circle at 50% 0%,
                            rgba(245, 130, 32, 0.22),
                            transparent 60%
                        );
                    "
                    aria-hidden="true"
                />
            </template>
            <template #eyebrow>
                <Badge variant="info">Get in touch</Badge>
            </template>
            <template #title>
                <h1
                    class="text-4xl font-extrabold tracking-tight text-white sm:text-5xl"
                >
                    Let&rsquo;s Talk Quality
                </h1>
            </template>
            <template #subtitle>
                Land, livestock, fashion or tech — tell us what you need and
                we&rsquo;ll get back to you within 24 hours.
            </template>
        </Hero>

        <Section>
            <div class="grid gap-10 lg:grid-cols-12 lg:gap-12">
                <!-- Form -->
                <div class="lg:col-span-7">
                    <h2 class="text-xl font-extrabold tracking-tight">
                        Send us a message
                    </h2>

                    <div
                        v-if="flashSuccess"
                        class="mt-5 flex items-start gap-3 rounded-2xl border border-green-300 bg-green-50 p-5 text-sm font-medium text-green-900"
                    >
                        <CheckCircle2 class="mt-0.5 h-5 w-5 shrink-0 text-green-700" />
                        <span>{{ flashSuccess }}</span>
                    </div>

                    <form
                        class="mt-5 space-y-4 rounded-3xl bg-white p-6 shadow-md ring-1 ring-black/5 sm:p-7"
                        @submit.prevent="submit"
                    >
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="sm:col-span-2">
                                <label class="form-label" for="name">Full name</label>
                                <input
                                    id="name"
                                    v-model="form.name"
                                    type="text"
                                    autocomplete="name"
                                    class="form-input"
                                />
                                <p v-if="form.errors.name" class="form-error">
                                    {{ form.errors.name }}
                                </p>
                            </div>
                            <div>
                                <label class="form-label" for="email">Email</label>
                                <input
                                    id="email"
                                    v-model="form.email"
                                    type="email"
                                    autocomplete="email"
                                    class="form-input"
                                />
                                <p v-if="form.errors.email" class="form-error">
                                    {{ form.errors.email }}
                                </p>
                            </div>
                            <div>
                                <label class="form-label" for="phone">Phone</label>
                                <input
                                    id="phone"
                                    :value="form.phone"
                                    type="tel"
                                    inputmode="tel"
                                    placeholder="0801 234 5678"
                                    autocomplete="tel"
                                    class="form-input"
                                    @input="onPhoneInput"
                                />
                                <p v-if="form.errors.phone" class="form-error">
                                    {{ form.errors.phone }}
                                </p>
                            </div>
                            <div class="sm:col-span-2">
                                <label class="form-label" for="subject">Subject</label>
                                <select
                                    id="subject"
                                    v-model="form.subject"
                                    class="form-input"
                                >
                                    <option
                                        v-for="(label, value) in subjects"
                                        :key="value"
                                        :value="value"
                                    >
                                        {{ label }}
                                    </option>
                                </select>
                                <p v-if="form.errors.subject" class="form-error">
                                    {{ form.errors.subject }}
                                </p>
                            </div>
                            <div class="sm:col-span-2">
                                <label class="form-label" for="message">Message</label>
                                <textarea
                                    id="message"
                                    v-model="form.message"
                                    rows="5"
                                    class="form-input"
                                    placeholder="Tell us a bit about what you need."
                                />
                                <p v-if="form.errors.message" class="form-error">
                                    {{ form.errors.message }}
                                </p>
                            </div>
                        </div>

                        <!-- Honeypot — visually hidden but present in the DOM. -->
                        <div class="hp-field" aria-hidden="true">
                            <label for="website">Leave this empty</label>
                            <input
                                id="website"
                                v-model="form.website"
                                type="text"
                                tabindex="-1"
                                autocomplete="off"
                            />
                        </div>

                        <Button
                            type="submit"
                            size="lg"
                            class="w-full sm:w-auto"
                            :disabled="form.processing"
                        >
                            <Send class="h-4 w-4" />
                            {{ form.processing ? 'Sending…' : 'Send message' }}
                        </Button>
                    </form>
                </div>

                <!-- Contact info -->
                <aside class="lg:col-span-5">
                    <div class="rounded-3xl bg-brand-dark p-6 text-white shadow-md sm:p-7">
                        <p class="text-xs font-semibold uppercase tracking-wider text-brand-orange">
                            Quick response
                        </p>
                        <h3 class="mt-1 text-xl font-extrabold tracking-tight">
                            WhatsApp is fastest
                        </h3>
                        <p class="mt-2 text-sm text-white/70">
                            For same-day replies, message us on WhatsApp — our
                            team is available throughout the day.
                        </p>
                        <a
                            :href="`https://wa.me/${whatsappNumber}`"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="mt-4 inline-flex items-center gap-2 rounded-full bg-[#25D366] px-5 py-3 text-sm font-semibold text-white transition-transform hover:scale-[1.02]"
                        >
                            <Sparkles class="h-4 w-4" />
                            Chat on WhatsApp
                        </a>
                    </div>

                    <div
                        class="mt-5 rounded-3xl bg-white p-6 shadow-md ring-1 ring-black/5 sm:p-7"
                    >
                        <p class="text-xs font-semibold uppercase tracking-wider text-brand-dark/55">
                            Call us
                        </p>
                        <ul class="mt-2 space-y-3 text-sm">
                            <li
                                v-for="phone in phones"
                                :key="phone.tel"
                                class="flex items-center justify-between gap-3"
                            >
                                <a
                                    :href="`tel:${phone.tel}`"
                                    class="flex items-center gap-2 font-bold text-brand-dark transition-colors hover:text-brand-orange"
                                >
                                    <Phone class="h-4 w-4 text-brand-orange" />
                                    {{ phone.display }}
                                </a>
                                <a
                                    :href="`https://wa.me/${phone.wa}`"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="text-xs font-semibold text-brand-orange hover:text-brand-orangeDark"
                                >
                                    WhatsApp
                                </a>
                            </li>
                        </ul>

                        <p class="mt-5 text-xs font-semibold uppercase tracking-wider text-brand-dark/55">
                            Email
                        </p>
                        <a
                            href="mailto:admin@blomfree.com"
                            class="mt-1 inline-flex items-center gap-2 text-sm font-bold text-brand-dark transition-colors hover:text-brand-orange"
                        >
                            <MailIcon class="h-4 w-4 text-brand-orange" />
                            admin@blomfree.com
                        </a>

                        <p class="mt-5 text-xs font-semibold uppercase tracking-wider text-brand-dark/55">
                            Follow
                        </p>
                        <div class="mt-2 flex gap-2">
                            <a
                                href="https://instagram.com/Saturday_Emomotimi_Charles"
                                target="_blank"
                                rel="noopener noreferrer"
                                aria-label="Instagram"
                                class="rounded-full bg-brand-cream p-2.5 text-brand-dark/70 transition-colors hover:bg-brand-orange hover:text-white"
                            >
                                <Instagram class="h-4 w-4" />
                            </a>
                            <a
                                href="https://tiktok.com/@Saturday_Emomotimi_Charles"
                                target="_blank"
                                rel="noopener noreferrer"
                                aria-label="TikTok"
                                class="rounded-full bg-brand-cream p-2.5 text-brand-dark/70 transition-colors hover:bg-brand-orange hover:text-white"
                            >
                                <svg viewBox="0 0 24 24" fill="currentColor" class="h-4 w-4" aria-hidden="true">
                                    <path
                                        d="M16.5 3c.3 2.3 1.6 3.9 3.9 4.1v2.7c-1.4.1-2.7-.3-3.9-1v6.6c0 4.2-4.6 6.9-8.2 4.8-2.3-1.4-3-4.4-1.6-6.8 1-1.7 2.9-2.6 4.8-2.4v2.8c-.5-.1-1-.1-1.5.1-1.4.5-1.9 2.2-1 3.4.9 1.2 2.9.9 3.3-.6.07-.27.1-.55.1-.83V3h3.6z"
                                    />
                                </svg>
                            </a>
                            <a
                                href="https://facebook.com/Saturday_Emomotimi_Charles"
                                target="_blank"
                                rel="noopener noreferrer"
                                aria-label="Facebook"
                                class="rounded-full bg-brand-cream p-2.5 text-brand-dark/70 transition-colors hover:bg-brand-orange hover:text-white"
                            >
                                <Facebook class="h-4 w-4" />
                            </a>
                        </div>
                    </div>
                </aside>
            </div>
        </Section>

        <!-- FAQ -->
        <Section width="narrow">
            <div class="mx-auto max-w-2xl text-center">
                <Badge variant="info">FAQ</Badge>
                <h2
                    class="mt-3 text-3xl font-extrabold tracking-tight sm:text-4xl"
                >
                    Quick answers
                </h2>
                <p class="mt-3 text-base text-brand-dark/65">
                    Common questions from across BLOMFREE &amp; CO. — if yours
                    isn&rsquo;t here, drop us a note above.
                </p>
            </div>

            <div class="mx-auto mt-8 max-w-2xl space-y-3">
                <details
                    v-for="(faq, i) in faqs"
                    :key="i"
                    class="group rounded-2xl bg-white shadow-sm ring-1 ring-black/5"
                >
                    <summary
                        class="flex cursor-pointer list-none items-center justify-between gap-3 p-5 text-left font-bold tracking-tight"
                    >
                        <span>{{ faq.q }}</span>
                        <span
                            class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-brand-orange/10 text-brand-orange transition-transform group-open:rotate-45"
                        >
                            +
                        </span>
                    </summary>
                    <p
                        class="border-t border-black/5 px-5 py-4 text-sm leading-relaxed text-brand-dark/70"
                    >
                        {{ faq.a }}
                    </p>
                </details>
            </div>
        </Section>
    </AppLayout>
</template>

<style scoped>
.form-label {
    display: block;
    margin-bottom: 0.375rem;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: rgb(26 26 26 / 0.6);
}
.form-input {
    width: 100%;
    border-radius: 0.75rem;
    border-color: rgb(26 26 26 / 0.15);
    font-size: 0.875rem;
}
.form-input:focus {
    border-color: #f58220;
    --tw-ring-color: #f58220;
}
.form-error {
    margin-top: 0.375rem;
    font-size: 0.75rem;
    font-weight: 500;
    color: #dc2626;
}
.hp-field {
    position: absolute;
    left: -10000px;
    top: auto;
    width: 1px;
    height: 1px;
    overflow: hidden;
}
</style>
