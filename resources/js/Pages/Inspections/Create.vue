<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { CalendarCheck, ChevronLeft } from 'lucide-vue-next';
import { VueDatePicker } from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css';
import AppLayout from '@/Layouts/AppLayout.vue';
import Section from '@/Components/Section.vue';
import Button from '@/Components/Button.vue';
import { formatNigerianPhone } from '@/lib/format';

interface InspectionSubject {
    type: 'land' | 'animal';
    id: number;
    title: string;
    subtitle: string;
    meta: string | null;
    image: string | null;
    back_url: string;
    kind: 'site' | 'animal';
}

const props = defineProps<{
    subject: InspectionSubject;
    timeSlots: string[];
    disabledWeekDays: number[];
}>();

const form = useForm({
    inspectable_type: props.subject.type,
    inspectable_id: props.subject.id,
    customer_name: '',
    customer_email: '',
    customer_phone: '',
    preferred_date: '',
    preferred_time_slot: '',
    alternate_date: '',
    party_size: 1,
    notes: '',
});

const minDate = new Date();

const inspectionLabel = computed(() =>
    props.subject.kind === 'site' ? 'Site Inspection' : 'Inspection',
);

const headline = computed(() =>
    props.subject.kind === 'site'
        ? `Book a Site Inspection, ${props.subject.title}`
        : `Book an Inspection, ${props.subject.title}`,
);

function onPhoneInput(event: Event): void {
    const target = event.target as HTMLInputElement;
    form.customer_phone = formatNigerianPhone(target.value);
}

function submit(): void {
    form.post('/inspections', {
        preserveScroll: true,
    });
}
</script>

<template>
    <Head :title="`Book Inspection — ${subject.title}`" />

    <AppLayout>
        <Section width="narrow">
            <Link
                :href="subject.back_url"
                class="inline-flex items-center gap-1.5 text-sm font-semibold text-brand-dark/60 transition-colors hover:text-brand-orange"
            >
                <ChevronLeft class="h-4 w-4" />
                Back to listing
            </Link>

            <div class="mt-6">
                <span
                    class="inline-flex items-center gap-1.5 rounded-full bg-brand-orange/10 px-3 py-1 text-xs font-semibold text-brand-orange"
                >
                    <CalendarCheck class="h-3.5 w-3.5" />
                    {{ inspectionLabel }}
                </span>
                <h1
                    class="mt-3 text-3xl font-extrabold tracking-tight sm:text-4xl"
                >
                    {{ headline }}
                </h1>
                <p class="mt-3 text-base text-brand-dark/65">
                    Pick a date and time that works for you. We confirm every
                    request within 24 hours and send you the meeting details
                    once approved.
                </p>
            </div>

            <!-- Subject summary -->
            <div
                class="mt-6 flex items-center gap-4 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-black/5"
            >
                <div
                    class="h-16 w-16 shrink-0 overflow-hidden rounded-xl bg-brand-dark/5"
                >
                    <img
                        v-if="subject.image"
                        :src="subject.image"
                        :alt="subject.title"
                        class="h-full w-full object-cover"
                    />
                </div>
                <div class="min-w-0">
                    <p class="truncate font-bold tracking-tight">
                        {{ subject.title }}
                    </p>
                    <p class="mt-0.5 truncate text-sm text-brand-dark/60">
                        {{ subject.subtitle }}
                    </p>
                    <p
                        v-if="subject.meta"
                        class="mt-0.5 truncate text-xs text-brand-dark/45"
                    >
                        {{ subject.meta }}
                    </p>
                </div>
            </div>

            <!-- Booking form -->
            <form
                class="mt-8 space-y-5 rounded-3xl bg-white p-6 shadow-md ring-1 ring-black/5 sm:p-8"
                @submit.prevent="submit"
            >
                <div class="grid gap-5 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="form-label" for="customer_name">
                            Full name
                        </label>
                        <input
                            id="customer_name"
                            v-model="form.customer_name"
                            type="text"
                            class="form-input"
                            autocomplete="name"
                        />
                        <p v-if="form.errors.customer_name" class="form-error">
                            {{ form.errors.customer_name }}
                        </p>
                    </div>

                    <div>
                        <label class="form-label" for="customer_email">
                            Email address
                        </label>
                        <input
                            id="customer_email"
                            v-model="form.customer_email"
                            type="email"
                            class="form-input"
                            autocomplete="email"
                        />
                        <p
                            v-if="form.errors.customer_email"
                            class="form-error"
                        >
                            {{ form.errors.customer_email }}
                        </p>
                    </div>

                    <div>
                        <label class="form-label" for="customer_phone">
                            Phone number
                        </label>
                        <input
                            id="customer_phone"
                            :value="form.customer_phone"
                            type="tel"
                            inputmode="tel"
                            placeholder="0801 234 5678"
                            class="form-input"
                            autocomplete="tel"
                            @input="onPhoneInput"
                        />
                        <p
                            v-if="form.errors.customer_phone"
                            class="form-error"
                        >
                            {{ form.errors.customer_phone }}
                        </p>
                    </div>

                    <div>
                        <label class="form-label">Preferred date</label>
                        <VueDatePicker
                            v-model="form.preferred_date"
                            model-type="yyyy-MM-dd"
                            :enable-time-picker="false"
                            :min-date="minDate"
                            :disabled-week-days="disabledWeekDays"
                            :clearable="false"
                            auto-apply
                            format="dd MMM yyyy"
                            placeholder="Select a date"
                        />
                        <p
                            v-if="form.errors.preferred_date"
                            class="form-error"
                        >
                            {{ form.errors.preferred_date }}
                        </p>
                    </div>

                    <div>
                        <label class="form-label" for="preferred_time_slot">
                            Preferred time slot
                        </label>
                        <select
                            id="preferred_time_slot"
                            v-model="form.preferred_time_slot"
                            class="form-input"
                        >
                            <option value="" disabled>
                                Select a time slot
                            </option>
                            <option
                                v-for="slot in timeSlots"
                                :key="slot"
                                :value="slot"
                            >
                                {{ slot }}
                            </option>
                        </select>
                        <p
                            v-if="form.errors.preferred_time_slot"
                            class="form-error"
                        >
                            {{ form.errors.preferred_time_slot }}
                        </p>
                    </div>

                    <div>
                        <label class="form-label">
                            Alternate date
                            <span class="text-brand-dark/40">(optional)</span>
                        </label>
                        <VueDatePicker
                            v-model="form.alternate_date"
                            model-type="yyyy-MM-dd"
                            :enable-time-picker="false"
                            :min-date="minDate"
                            :disabled-week-days="disabledWeekDays"
                            auto-apply
                            format="dd MMM yyyy"
                            placeholder="Select a date"
                        />
                        <p
                            v-if="form.errors.alternate_date"
                            class="form-error"
                        >
                            {{ form.errors.alternate_date }}
                        </p>
                    </div>

                    <div>
                        <label class="form-label" for="party_size">
                            Party size
                        </label>
                        <input
                            id="party_size"
                            v-model.number="form.party_size"
                            type="number"
                            min="1"
                            max="20"
                            class="form-input"
                        />
                        <p v-if="form.errors.party_size" class="form-error">
                            {{ form.errors.party_size }}
                        </p>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="form-label" for="notes">
                            Notes
                            <span class="text-brand-dark/40">(optional)</span>
                        </label>
                        <textarea
                            id="notes"
                            v-model="form.notes"
                            rows="3"
                            class="form-input"
                            placeholder="Anything we should know ahead of the visit?"
                        />
                        <p v-if="form.errors.notes" class="form-error">
                            {{ form.errors.notes }}
                        </p>
                    </div>
                </div>

                <div
                    class="flex flex-col gap-3 border-t border-black/5 pt-5 sm:flex-row sm:items-center sm:justify-between"
                >
                    <p class="text-xs text-brand-dark/50">
                        By submitting, you agree to be contacted about this
                        inspection.
                    </p>
                    <Button
                        type="submit"
                        size="lg"
                        :disabled="form.processing"
                    >
                        <CalendarCheck class="h-4 w-4" />
                        {{
                            form.processing
                                ? 'Submitting…'
                                : 'Request Inspection'
                        }}
                    </Button>
                </div>
            </form>
        </Section>
    </AppLayout>
</template>

<style>
.dp__theme_light {
    --dp-primary-color: #f58220;
    --dp-border-radius: 0.75rem;
}
</style>

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
</style>
