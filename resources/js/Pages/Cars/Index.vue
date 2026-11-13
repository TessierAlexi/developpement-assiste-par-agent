<script setup>
import CarCard from '@/Components/CarCard.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Pagination from '@/Components/Pagination.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
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
});

const search = ref(props.filters.search);

const searchCars = (term) => {
    router.get(
        route('cars.index'),
        term ? { search: term } : {},
        { preserveState: true, replace: true },
    );
};

const clearSearch = () => {
    search.value = '';
    searchCars('');
};
</script>

<template>
    <Head title="Cars" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Cars
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <form
                    class="flex flex-col gap-3 bg-white p-4 shadow-sm sm:flex-row sm:items-end sm:rounded-lg"
                    role="search"
                    @submit.prevent="searchCars(search)"
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
                    <div class="flex gap-2">
                        <PrimaryButton>Search</PrimaryButton>
                        <SecondaryButton v-if="filters.search" @click="clearSearch">
                            Clear
                        </SecondaryButton>
                    </div>
                </form>

                <p class="px-4 text-sm text-gray-600 sm:px-0">
                    {{ cars.total }} {{ cars.total === 1 ? 'car' : 'cars' }}
                    <template v-if="filters.search">
                        matching “{{ filters.search }}”
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
