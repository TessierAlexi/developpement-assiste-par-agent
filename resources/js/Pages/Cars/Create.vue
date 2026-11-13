<script setup>
import CarForm from '@/Components/CarForm.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    defaults: {
        type: Object,
        required: true,
    },
    statuses: {
        type: Array,
        required: true,
    },
    drivetrains: {
        type: Array,
        required: true,
    },
    equipmentCatalogue: {
        type: Array,
        required: true,
    },
});
</script>

<template>
    <Head title="Add a car" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Add a car
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
                <CarForm
                    :statuses="statuses"
                    :drivetrains="drivetrains"
                    :equipment-catalogue="equipmentCatalogue"
                    :car="defaults"
                    method="post"
                    :action="route('cars.store')"
                    :cancel-url="route('cars.index')"
                    submit-label="Add car"
                />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
