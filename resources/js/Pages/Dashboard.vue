<script setup>
import CarPhoto from '@/Components/CarPhoto.vue';
import CarStatusBadge from '@/Components/CarStatusBadge.vue';
import InventoryGauge from '@/Components/InventoryGauge.vue';
import { carTitle, formatDate, formatPrice } from '@/formatters';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    gauges: {
        type: Object,
        required: true,
    },
    tiles: {
        type: Object,
        required: true,
    },
    oldestCars: {
        type: Array,
        required: true,
    },
});

const MISSING = '—';

const pluralize = (count, singular, plural) => `${count} ${count === 1 ? singular : plural}`;

const countOf = (status) => props.tiles.countsByStatus.find((entry) => entry.value === status)?.count ?? 0;

const availableShare = computed(() => {
    const { value, target } = props.gauges.availableShare;
    const inInventory = countOf('available') + countOf('on_hold');

    return {
        value,
        target,
        display: value === null ? MISSING : `${value}%`,
        caption: value === null ? 'No cars in inventory' : `${countOf('available')} of ${pluralize(inInventory, 'unsold car', 'unsold cars')}`,
    };
});

const averageDays = computed(() => {
    const { value, target } = props.gauges.averageDaysInInventory;
    const standing = value !== null && value > target ? 'over target' : 'within target';

    return {
        value,
        target,
        display: value === null ? MISSING : String(value),
        caption: value === null ? 'No cars in inventory' : `${pluralize(value, 'day', 'days')} · ${standing} of ${target}`,
    };
});

const sales = computed(() => {
    const { value, target } = props.gauges.salesThisMonth;

    return {
        value,
        target,
        display: String(value),
        caption: value >= target ? `Goal of ${target} reached` : `${target - value} to go to reach ${target}`,
    };
});
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-800"
            >
                Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <section aria-label="Inventory gauges" class="grid grid-cols-1 gap-6 md:grid-cols-3">
                    <InventoryGauge label="Inventory available" v-bind="availableShare" />
                    <InventoryGauge label="Average days in inventory" v-bind="averageDays" />
                    <InventoryGauge label="Sales this month" v-bind="sales" />
                </section>

                <section aria-label="Inventory figures" class="grid grid-cols-1 gap-6 md:grid-cols-3">
                    <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                        <h3 class="text-sm font-medium text-gray-500">Available inventory value</h3>
                        <p class="mt-2 text-3xl font-semibold text-gray-900">
                            {{ formatPrice(tiles.availableInventoryValue) }}
                        </p>
                        <p class="mt-1 text-sm text-gray-500">Asking prices of available cars</p>
                    </div>

                    <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                        <h3 class="text-sm font-medium text-gray-500">Cars by status</h3>
                        <ul class="mt-2 space-y-2">
                            <li v-for="entry in tiles.countsByStatus" :key="entry.value">
                                <Link
                                    :href="route('cars.index', { status: entry.value })"
                                    class="flex items-center justify-between rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                >
                                    <CarStatusBadge :status="entry.value" />
                                    <span class="text-lg font-semibold text-gray-900">{{ entry.count }}</span>
                                </Link>
                            </li>
                        </ul>
                    </div>

                    <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                        <h3 class="text-sm font-medium text-gray-500">Average margin on sold cars</h3>
                        <p class="mt-2 text-3xl font-semibold text-gray-900">
                            {{ formatPrice(tiles.averageSoldMargin) }}
                        </p>
                        <p class="mt-1 text-sm text-gray-500">Asking price minus cost, priced cars only</p>
                    </div>
                </section>

                <section class="bg-white shadow-sm sm:rounded-lg">
                    <h3 class="border-b border-gray-100 px-6 py-4 font-semibold text-gray-900">
                        Longest in inventory
                    </h3>
                    <ul v-if="oldestCars.length" class="divide-y divide-gray-100">
                        <li v-for="car in oldestCars" :key="car.id">
                            <Link
                                :href="route('cars.show', car.id)"
                                class="flex items-center gap-4 px-6 py-3 transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500"
                            >
                                <CarPhoto
                                    :src="car.photo_url"
                                    :alt="`${car.make} ${car.model}`"
                                    class="hidden h-12 w-16 shrink-0 rounded sm:block [&_span]:hidden [&_svg]:h-6 [&_svg]:w-6"
                                />
                                <div class="min-w-0 flex-1">
                                    <p class="truncate font-medium text-gray-900">{{ carTitle(car) }}</p>
                                    <p class="text-xs text-gray-500">
                                        {{ car.stock_number }} · Received {{ formatDate(car.received_on) }}
                                    </p>
                                </div>
                                <CarStatusBadge :status="car.status" />
                                <span class="w-20 text-right text-sm font-semibold text-gray-900">
                                    {{ pluralize(car.days_in_inventory, 'day', 'days') }}
                                </span>
                            </Link>
                        </li>
                    </ul>
                    <p v-else class="p-6 text-center text-gray-600">No cars in inventory.</p>
                </section>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
