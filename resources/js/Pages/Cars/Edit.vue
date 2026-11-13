<script setup>
import CarForm from '@/Components/CarForm.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { carTitle } from '@/formatters';
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    car: {
        type: Object,
        required: true,
    },
});

const title = computed(() => `Edit ${carTitle(props.car)}`);
</script>

<template>
    <Head :title="title" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                {{ title }} <span class="font-normal text-gray-500">{{ car.stock_number }}</span>
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
                <CarForm
                    :car="car"
                    method="put"
                    :action="route('cars.update', car.id)"
                    :cancel-url="route('cars.show', car.id)"
                    submit-label="Save changes"
                />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
