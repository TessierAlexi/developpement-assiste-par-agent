<script setup>
import CarPhoto from '@/Components/CarPhoto.vue';
import CarStatusBadge from '@/Components/CarStatusBadge.vue';
import DangerButton from '@/Components/DangerButton.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {
    carTitle,
    formatConsumption,
    formatDate,
    formatDisplacement,
    formatMileage,
    formatPrice,
    formatText,
} from '@/formatters';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    car: {
        type: Object,
        required: true,
    },
    statuses: {
        type: Array,
        required: true,
    },
});

const isPhotoEnlarged = ref(false);
const isConfirmingDeletion = ref(false);

const statusForm = useForm({ status: props.car.status });
const deleteForm = useForm({});

const changeStatus = () => {
    statusForm.patch(route('cars.status.update', props.car.id), { preserveScroll: true });
};

const deleteCar = () => {
    deleteForm.delete(route('cars.destroy', props.car.id), {
        onFinish: () => {
            isConfirmingDeletion.value = false;
        },
    });
};

const title = computed(() => carTitle(props.car));
const photoAlt = computed(() => `${props.car.make} ${props.car.model}`);

const specifications = computed(() => [
    ['Stock number', props.car.stock_number],
    ['VIN', formatText(props.car.vin)],
    ['Received on', formatDate(props.car.received_on)],
    ...(props.car.sold_on ? [['Sold on', formatDate(props.car.sold_on)]] : []),
    ['Year', props.car.year],
    ['Trim', props.car.trim],
    ['Body type', props.car.body_type],
    ['Doors', formatText(props.car.doors)],
    ['Seats', formatText(props.car.seats)],
    ['Exterior colour', formatText(props.car.colour)],
    ['Interior colour', formatText(props.car.interior_colour)],
    ['Mileage', formatMileage(props.car.mileage_km)],
    ['Engine', formatText(props.car.engine)],
    ['Displacement', formatDisplacement(props.car.engine_displacement_l)],
    ['Cylinders', formatText(props.car.cylinders)],
    ['Transmission', props.car.transmission],
    ['Drivetrain', formatText(props.car.drivetrain)],
    ['Fuel', props.car.fuel_type],
    ['City consumption', formatConsumption(props.car.city_l_per_100km)],
    ['Highway consumption', formatConsumption(props.car.highway_l_per_100km)],
    ['Cost price', formatPrice(props.car.cost_price)],
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
                <div class="flex items-center gap-4">
                    <Link
                        :href="route('cars.index')"
                        class="text-sm text-gray-600 underline hover:text-gray-900"
                    >
                        ← Back to cars
                    </Link>
                    <Link
                        :href="route('cars.edit', car.id)"
                        class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 shadow-sm transition duration-150 ease-in-out hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    >
                        Edit
                    </Link>
                    <DangerButton @click="isConfirmingDeletion = true">Delete</DangerButton>
                </div>
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
                    <div class="flex items-center justify-between gap-4">
                        <p class="text-3xl font-semibold text-indigo-600">
                            {{ formatPrice(car.price) }}
                        </p>
                        <CarStatusBadge :status="car.status" />
                    </div>

                    <form class="flex items-end gap-2" @submit.prevent="changeStatus">
                        <div class="flex-1">
                            <InputLabel for="status" value="Status" />
                            <select
                                id="status"
                                v-model="statusForm.status"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                                <option v-for="option in statuses" :key="option.value" :value="option.value">
                                    {{ option.label }}
                                </option>
                            </select>
                            <InputError :message="statusForm.errors.status" class="mt-2" />
                        </div>
                        <PrimaryButton
                            :class="{ 'opacity-25': statusForm.processing }"
                            :disabled="statusForm.processing || statusForm.status === car.status"
                        >
                            Update status
                        </PrimaryButton>
                    </form>

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
                    <p v-else class="text-sm italic text-gray-500">
                        No description provided.
                    </p>

                    <div v-if="car.internal_notes" class="rounded-md bg-amber-50 p-4">
                        <h3 class="text-sm font-medium text-amber-800">Internal notes</h3>
                        <p class="mt-1 whitespace-pre-line text-sm text-amber-700">{{ car.internal_notes }}</p>
                    </div>
                </section>
            </div>
        </div>

        <Modal :show="isPhotoEnlarged" max-width="2xl" @close="isPhotoEnlarged = false">
            <CarPhoto :src="car.photo_url" :alt="photoAlt" />
            <div class="flex justify-end p-4">
                <SecondaryButton @click="isPhotoEnlarged = false">Close</SecondaryButton>
            </div>
        </Modal>

        <Modal :show="isConfirmingDeletion" max-width="md" @close="isConfirmingDeletion = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    Delete {{ title }} ({{ car.stock_number }})?
                </h2>
                <p class="mt-1 text-sm text-gray-600">
                    The car will be permanently removed from the inventory.
                </p>
                <div class="mt-6 flex justify-end">
                    <SecondaryButton @click="isConfirmingDeletion = false">Cancel</SecondaryButton>
                    <DangerButton
                        class="ms-3"
                        :class="{ 'opacity-25': deleteForm.processing }"
                        :disabled="deleteForm.processing"
                        @click="deleteCar"
                    >
                        Delete car
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
