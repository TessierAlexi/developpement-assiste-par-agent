<script setup>
import CarPhoto from '@/Components/CarPhoto.vue';
import CarStatusBadge from '@/Components/CarStatusBadge.vue';
import { carTitle, formatMileage, formatPrice } from '@/formatters';
import { Link } from '@inertiajs/vue3';

defineProps({
    car: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <Link
        :href="route('cars.show', car.id)"
        class="group block overflow-hidden rounded-lg bg-white shadow-sm transition hover:shadow-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
    >
        <CarPhoto
            :src="car.photo_url"
            :alt="`${car.make} ${car.model}`"
            class="aspect-[4/3] transition group-hover:opacity-90"
        />

        <div class="p-4">
            <div class="flex items-start justify-between gap-2">
                <h3 class="font-semibold text-gray-900">{{ carTitle(car) }}</h3>
                <CarStatusBadge :status="car.status" />
            </div>
            <p class="text-sm text-gray-500">
                {{ car.trim }} · {{ car.body_type }}
            </p>
            <p class="text-xs text-gray-400">{{ car.stock_number }}</p>

            <div class="mt-3 flex items-baseline justify-between">
                <span class="text-lg font-semibold text-indigo-600">
                    {{ formatPrice(car.price) }}
                </span>
                <span class="text-sm text-gray-500">
                    {{ formatMileage(car.mileage_km) }}
                </span>
            </div>
        </div>
    </Link>
</template>
