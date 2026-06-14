<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import {
    Clock,
    CheckCircle2,
    XCircle,
    CalendarX,
    MapPin,
    Phone,
} from 'lucide-vue-next';
import AppLayout from '@/Layouts/AppLayout.vue';
import Section from '@/Components/Section.vue';
import Card from '@/Components/Card.vue';
import Badge from '@/Components/Badge.vue';
import Button from '@/Components/Button.vue';
import { formatDate } from '@/lib/format';
import type { InspectionStatus } from '@/types/models';

interface InspectionView {
    reference: string;
    status: InspectionStatus;
    customer_name: string;
    customer_email: string;
    customer_phone: string;
    preferred_date: string | null;
    preferred_time_slot: string;
    alternate_date: string | null;
    party_size: number;
    notes: string | null;
    created_at: string | null;
    meeting_address: string | null;
    meeting_instructions: string | null;
}

interface PropertyView {
    type: string;
    title: string;
    location: string | null;
    cover_url: string | null;
    url: string | null;
}

const props = defineProps<{
    inspection: InspectionView;
    property: PropertyView | null;
}>();

interface StatusMeta {
    label: string;
    badge: 'success' | 'warning' | 'neutral';
    icon: typeof Clock;
    title: string;
    message: string;
}

const STATUS_CONFIG: Record<InspectionStatus, StatusMeta> = {
    pending: {
        label: 'Pending Approval',
        badge: 'warning',
        icon: Clock,
        title: 'Pending approval',
        message:
            "We've received your request and our team will confirm it within 24 hours. Check back here any time using your reference.",
    },
    approved: {
        label: 'Approved',
        badge: 'success',
        icon: CheckCircle2,
        title: "You're confirmed",
        message:
            'Your inspection has been approved. The meeting details are below — please arrive on time and bring a valid ID.',
    },
    rejected: {
        label: 'Not Approved',
        badge: 'neutral',
        icon: XCircle,
        title: 'Request not approved',
        message:
            "Unfortunately we couldn't confirm this inspection. You're welcome to book again for another date.",
    },
    completed: {
        label: 'Completed',
        badge: 'success',
        icon: CheckCircle2,
        title: 'Inspection completed',
        message:
            'This inspection has been completed. Thank you for visiting with BLOMFREE Real Estate.',
    },
    no_show: {
        label: 'No-Show',
        badge: 'neutral',
        icon: CalendarX,
        title: 'Marked as no-show',
        message:
            'This inspection was marked as a no-show. Book again whenever you are ready.',
    },
};

const meta = computed<StatusMeta>(() => STATUS_CONFIG[props.inspection.status]);
</script>

<template>
    <Head :title="`Inspection ${inspection.reference}`" />

    <AppLayout>
        <Section width="narrow">
            <!-- Status header -->
            <div class="text-center">
                <p
                    class="text-xs font-semibold uppercase tracking-wider text-brand-dark/45"
                >
                    Inspection Reference
                </p>
                <p
                    class="mt-1 text-3xl font-extrabold tracking-tight text-brand-orange sm:text-4xl"
                >
                    {{ inspection.reference }}
                </p>
                <div class="mt-4 flex justify-center">
                    <Badge :variant="meta.badge">
                        <component :is="meta.icon" class="h-3.5 w-3.5" />
                        {{ meta.label }}
                    </Badge>
                </div>
            </div>

            <!-- Status message -->
            <Card :hover="false" class="mt-8 p-6 sm:p-8">
                <h1 class="text-xl font-extrabold tracking-tight">
                    {{ meta.title }}
                </h1>
                <p class="mt-2 text-sm leading-relaxed text-brand-dark/65">
                    {{ meta.message }}
                </p>

                <!-- Meeting details (approved only) -->
                <div
                    v-if="
                        inspection.status === 'approved' &&
                        inspection.meeting_address
                    "
                    class="mt-5 rounded-2xl border-l-4 border-brand-orange bg-brand-cream p-5"
                >
                    <p
                        class="flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wider text-brand-dark/55"
                    >
                        <MapPin class="h-3.5 w-3.5" />
                        Meeting address
                    </p>
                    <p
                        class="mt-1.5 whitespace-pre-line text-sm font-medium text-brand-dark"
                    >
                        {{ inspection.meeting_address }}
                    </p>
                    <template v-if="inspection.meeting_instructions">
                        <p
                            class="mt-4 text-xs font-semibold uppercase tracking-wider text-brand-dark/55"
                        >
                            Instructions
                        </p>
                        <p
                            class="mt-1.5 whitespace-pre-line text-sm text-brand-dark/75"
                        >
                            {{ inspection.meeting_instructions }}
                        </p>
                    </template>
                </div>
            </Card>

            <!-- Property -->
            <div
                v-if="property"
                class="mt-5 flex items-center gap-4 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-black/5"
            >
                <div
                    class="h-16 w-16 shrink-0 overflow-hidden rounded-xl bg-brand-dark/5"
                >
                    <img
                        v-if="property.cover_url"
                        :src="property.cover_url"
                        :alt="property.title"
                        class="h-full w-full object-cover"
                    />
                </div>
                <div class="min-w-0 flex-1">
                    <p
                        class="text-xs font-semibold uppercase tracking-wider text-brand-dark/45"
                    >
                        {{ property.type }}
                    </p>
                    <p class="truncate font-bold tracking-tight">
                        {{ property.title }}
                    </p>
                    <p
                        v-if="property.location"
                        class="truncate text-sm text-brand-dark/60"
                    >
                        {{ property.location }}
                    </p>
                </div>
                <Link
                    v-if="property.url"
                    :href="property.url"
                    class="shrink-0 text-sm font-semibold text-brand-orange hover:text-brand-orangeDark"
                >
                    View &rarr;
                </Link>
            </div>

            <!-- Booking details -->
            <Card :hover="false" class="mt-5 p-6 sm:p-8">
                <h2 class="text-base font-bold tracking-tight">
                    Your booking details
                </h2>
                <dl class="mt-4 grid gap-x-6 gap-y-4 sm:grid-cols-2">
                    <div>
                        <dt class="detail-label">Name</dt>
                        <dd class="detail-value">
                            {{ inspection.customer_name }}
                        </dd>
                    </div>
                    <div>
                        <dt class="detail-label">Phone</dt>
                        <dd class="detail-value">
                            {{ inspection.customer_phone }}
                        </dd>
                    </div>
                    <div>
                        <dt class="detail-label">Email</dt>
                        <dd class="detail-value">
                            {{ inspection.customer_email }}
                        </dd>
                    </div>
                    <div>
                        <dt class="detail-label">Party size</dt>
                        <dd class="detail-value">
                            {{ inspection.party_size }}
                            {{
                                inspection.party_size === 1
                                    ? 'person'
                                    : 'people'
                            }}
                        </dd>
                    </div>
                    <div>
                        <dt class="detail-label">Preferred date</dt>
                        <dd class="detail-value">
                            {{ formatDate(inspection.preferred_date) }}
                        </dd>
                    </div>
                    <div>
                        <dt class="detail-label">Time slot</dt>
                        <dd class="detail-value">
                            {{ inspection.preferred_time_slot }}
                        </dd>
                    </div>
                    <div v-if="inspection.alternate_date">
                        <dt class="detail-label">Alternate date</dt>
                        <dd class="detail-value">
                            {{ formatDate(inspection.alternate_date) }}
                        </dd>
                    </div>
                    <div v-if="inspection.notes" class="sm:col-span-2">
                        <dt class="detail-label">Notes</dt>
                        <dd class="detail-value whitespace-pre-line">
                            {{ inspection.notes }}
                        </dd>
                    </div>
                </dl>
            </Card>

            <!-- Help / actions -->
            <div
                class="mt-6 flex flex-col items-center gap-3 text-center sm:flex-row sm:justify-center"
            >
                <Button href="/lands" variant="outline">
                    Browse more lands
                </Button>
                <Button
                    href="https://wa.me/2348103965317"
                    external
                    variant="ghost"
                >
                    <Phone class="h-4 w-4" />
                    Contact us about this booking
                </Button>
            </div>
        </Section>
    </AppLayout>
</template>

<style scoped>
.detail-label {
    font-size: 0.7rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: rgb(26 26 26 / 0.45);
}

.detail-value {
    margin-top: 0.125rem;
    font-size: 0.875rem;
    font-weight: 500;
    color: rgb(26 26 26 / 0.85);
}
</style>
