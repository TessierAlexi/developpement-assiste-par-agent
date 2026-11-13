<script setup>
import CarPhoto from '@/Components/CarPhoto.vue';
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { carTitle, formatMileage, formatPrice } from '@/formatters';
import { Head, Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    car: {
        type: Object,
        required: true,
    },
});

const isPhotoEnlarged = ref(false);

const title = computed(() => carTitle(props.car));
const photoAlt = computed(() => `${props.car.make} ${props.car.model}`);

const specifications = computed(() => [
    ['Year', props.car.year],
    ['Trim', props.car.trim],
    ['Body type', props.car.body_type],
    ['Fuel', props.car.fuel_type],
    ['Transmission', props.car.transmission],
    ['Colour', props.car.colour],
    ['Mileage', formatMileage(props.car.mileage_km)],
]);
</script>

<template>
    <Head :title="title" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between gap-4">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    {{ title }} <span class="font-normal text-gray-500">{{ car.trim }}</span>
                </h2>
                <Link
                    :href="route('cars.index')"
                    class="text-sm text-gray-600 underline hover:text-gray-900"
                >
                    ← Back to cars
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto grid max-w-7xl gap-6 sm:px-6 lg:grid-cols-5 lg:px-8">
                <figure class="overflow-hidden bg-white shadow-sm sm:rounded-lg lg:col-span-3">
                    <button
                        v-if="car.photo_url"
                        type="button"
                        class="block w-full cursor-zoom-in focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        aria-label="Enlarge photo"
                        @click="isPhotoEnlarged = true"
                    >
                        <CarPhoto :src="car.photo_url" :alt="photoAlt" class="aspect-[4/3]" />
                    </button>
                    <CarPhoto v-else :src="null" :alt="photoAlt" class="aspect-[4/3]" />

                    <figcaption
                        v-if="car.photo_url && car.photo_attribution_required"
                        class="px-4 py-3 text-xs text-gray-500"
                    >
                        Representative photo of the model by
                        <a :href="car.photo_source_url" class="underline hover:text-gray-700" target="_blank" rel="noopener">{{ car.photo_author }}</a>,
                        licensed under
                        <a :href="car.photo_licence_url" class="underline hover:text-gray-700" target="_blank" rel="noopener license">{{ car.photo_licence }}</a>,
                        via Wikimedia Commons.
                    </figcaption>
                </figure>

                <section class="space-y-6 bg-white p-6 shadow-sm sm:rounded-lg lg:col-span-2">
                    <p class="text-3xl font-semibold text-indigo-600">
                        {{ formatPrice(car.price) }}
                    </p>

                    <dl class="divide-y divide-gray-100">
                        <div
                            v-for="[label, value] in specifications"
                            :key="label"
                            class="flex justify-between py-2 text-sm"
                        >
                            <dt class="text-gray-500">{{ label }}</dt>
                            <dd class="font-medium text-gray-900">{{ value }}</dd>
                        </div>
                    </dl>

                    <p v-if="car.description" class="text-gray-700">
                        {{ car.description }}
                    </p>
                </section>
            </div>
        </div>

        <Modal :show="isPhotoEnlarged" max-width="2xl" @close="isPhotoEnlarged = false">
            <CarPhoto :src="car.photo_url" :alt="photoAlt" />
            <div class="flex justify-end p-4">
                <SecondaryButton @click="isPhotoEnlarged = false">Close</SecondaryButton>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
