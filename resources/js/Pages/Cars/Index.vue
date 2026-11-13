<script setup>
import CarCard from '@/Components/CarCard.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Pagination from '@/Components/Pagination.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    cars: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        required: true,
    },
    statuses: {
        type: Array,
        required: true,
    },
});

const search = ref(props.filters.search);
const status = ref(props.filters.status ?? '');

const applyFilters = () => {
    const query = Object.fromEntries(
        Object.entries({ search: search.value, status: status.value }).filter(([, value]) => value),
    );

    router.get(route('cars.index'), query, { preserveState: true, replace: true });
};

const clearFilters = () => {
    search.value = '';
    status.value = '';
    applyFilters();
};

const statusLabel = (value) => props.statuses.find((option) => option.value === value)?.label;
</script>

<template>
    <Head title="Cars" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between gap-4">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Cars
                </h2>
                <Link
                    :href="route('cars.create')"
                    class="inline-flex items-center rounded-md border border-transparent bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                >
                    Add car
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <form
                    class="flex flex-col gap-3 bg-white p-4 shadow-sm sm:flex-row sm:items-end sm:rounded-lg"
                    role="search"
                    @submit.prevent="applyFilters"
                >
                    <div class="flex-1">
                        <InputLabel for="search" value="Search by make, model or trim" />
                        <TextInput
                            id="search"
                            v-model="search"
                            type="search"
                            class="mt-1 block w-full"
                            placeholder="e.g. Civic"
                        />
                    </div>
                    <div class="sm:w-48">
                        <InputLabel for="status" value="Status" />
                        <select
                            id="status"
                            v-model="status"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            @change="applyFilters"
                        >
                            <option value="">All statuses</option>
                            <option v-for="option in statuses" :key="option.value" :value="option.value">
                                {{ option.label }}
                            </option>
                        </select>
                    </div>
                    <div class="flex gap-2">
                        <PrimaryButton>Search</PrimaryButton>
                        <SecondaryButton v-if="filters.search || filters.status" @click="clearFilters">
                            Clear
                        </SecondaryButton>
                    </div>
                </form>

                <p class="px-4 text-sm text-gray-600 sm:px-0">
                    {{ cars.total }} {{ cars.total === 1 ? 'car' : 'cars' }}
                    <template v-if="filters.search">
                        matching “{{ filters.search }}”
                    </template>
                    <template v-if="filters.status">
                        · {{ statusLabel(filters.status) }}
                    </template>
                </p>

                <div
                    v-if="cars.data.length"
                    class="grid grid-cols-1 gap-6 px-4 sm:grid-cols-2 sm:px-0 lg:grid-cols-3 xl:grid-cols-4"
                >
                    <CarCard v-for="car in cars.data" :key="car.id" :car="car" />
                </div>
                <div
                    v-else
                    class="bg-white p-6 text-center text-gray-600 shadow-sm sm:rounded-lg"
                >
                    No cars found.
                </div>

                <Pagination :links="cars.links" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
