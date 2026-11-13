<script setup>
import CarPhoto from '@/Components/CarPhoto.vue';
import CarPhotoCredit from '@/Components/CarPhotoCredit.vue';
import CarStatusBadge from '@/Components/CarStatusBadge.vue';
import DangerButton from '@/Components/DangerButton.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import Tabs from '@/Components/Tabs.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {
    carTitle,
    formatConsumption,
    formatDate,
    formatDateTime,
    formatDisplacement,
    formatMileage,
    formatPrice,
    formatText,
} from '@/formatters';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
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
    tabs: {
        type: Array,
        required: true,
    },
    activeTab: {
        type: String,
        required: true,
    },
    equipment: {
        type: Array,
        required: true,
    },
    statusChanges: {
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

/**
 * Show another tab and put it in the URL, so the link can be shared, without a round trip to the server.
 * The first tab is the default one and keeps the plain URL of the car.
 */
const showTab = (tab) => {
    const isDefaultTab = tab === props.tabs[0].value;

    router.replace({
        url: route('cars.show', { car: props.car.id, ...(isDefaultTab ? {} : { tab }) }),
        props: (currentProps) => ({ ...currentProps, activeTab: tab }),
        preserveScroll: true,
        preserveState: true,
    });
};

const title = computed(() => carTitle(props.car));
const photoAlt = computed(() => `${props.car.make} ${props.car.model}`);
const hasPhotoCredit = computed(() => Boolean(props.car.photo_url && props.car.photo_attribution_required));

const highlights = computed(() => [
    ['Stock number', props.car.stock_number],
    ['Mileage', formatMileage(props.car.mileage_km)],
    ['Body type', props.car.body_type],
    ['Transmission', props.car.transmission],
    ['Fuel', props.car.fuel_type],
    ['Exterior colour', formatText(props.car.colour)],
]);

const specifications = computed(() => [
    ['Stock number', props.car.stock_number],
    ['VIN', formatText(props.car.vin)],
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
]);

const pricing = computed(() => [
    ['Asking price', formatPrice(props.car.price)],
    ['Cost price', formatPrice(props.car.cost_price)],
    ['Received on', formatDate(props.car.received_on)],
    ...(props.car.sold_on ? [['Sold on', formatDate(props.car.sold_on)]] : []),
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
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <Tabs
                    :tabs="tabs"
                    :model-value="activeTab"
                    label="Car details"
                    @update:model-value="showTab"
                >
                    <template #overview>
                        <div class="grid gap-6 lg:grid-cols-5">
                            <figure class="overflow-hidden bg-white shadow-sm sm:rounded-lg lg:col-span-3">
                                <CarPhoto :src="car.photo_url" :alt="photoAlt" class="aspect-[4/3]" />
                                <CarPhotoCredit v-if="hasPhotoCredit" :car="car" />
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
                                        v-for="[label, value] in highlights"
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
                            </section>
                        </div>
                    </template>

                    <template #specifications>
                        <section class="bg-white p-6 shadow-sm sm:rounded-lg">
                            <dl class="grid gap-x-12 sm:grid-cols-2">
                                <div
                                    v-for="[label, value] in specifications"
                                    :key="label"
                                    class="flex justify-between border-b border-gray-100 py-2 text-sm"
                                >
                                    <dt class="text-gray-500">{{ label }}</dt>
                                    <dd class="font-medium text-gray-900">{{ value }}</dd>
                                </div>
                            </dl>
                        </section>
                    </template>

                    <template #equipment>
                        <section class="bg-white p-6 shadow-sm sm:rounded-lg">
                            <div v-if="equipment.length" class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                                <div v-for="group in equipment" :key="group.category">
                                    <h3 class="text-sm font-medium text-gray-900">{{ group.category }}</h3>
                                    <ul class="mt-2 list-inside list-disc space-y-1 text-sm text-gray-700">
                                        <li v-for="option in group.options" :key="option">{{ option }}</li>
                                    </ul>
                                </div>
                            </div>
                            <p v-else class="text-sm italic text-gray-500">
                                No equipment recorded. Check the options of this car on its
                                <Link :href="route('cars.edit', car.id)" class="underline hover:text-gray-700">edit page</Link>.
                            </p>
                        </section>
                    </template>

                    <template #pricing>
                        <section class="max-w-xl bg-white p-6 shadow-sm sm:rounded-lg">
                            <div class="flex items-center justify-between gap-4">
                                <p class="text-3xl font-semibold text-indigo-600">
                                    {{ formatPrice(car.price) }}
                                </p>
                                <CarStatusBadge :status="car.status" />
                            </div>
                            <dl class="mt-4 divide-y divide-gray-100">
                                <div
                                    v-for="[label, value] in pricing"
                                    :key="label"
                                    class="flex justify-between py-2 text-sm"
                                >
                                    <dt class="text-gray-500">{{ label }}</dt>
                                    <dd class="font-medium text-gray-900">{{ value }}</dd>
                                </div>
                            </dl>
                        </section>
                    </template>

                    <template #photos>
                        <figure class="max-w-3xl overflow-hidden bg-white shadow-sm sm:rounded-lg">
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
                            <CarPhotoCredit v-if="hasPhotoCredit" :car="car" />
                        </figure>
                    </template>

                    <template #history>
                        <section class="bg-white p-6 shadow-sm sm:rounded-lg">
                            <ol v-if="statusChanges.length" class="divide-y divide-gray-100">
                                <li
                                    v-for="change in statusChanges"
                                    :key="change.id"
                                    class="flex flex-wrap items-center justify-between gap-2 py-3 text-sm"
                                >
                                    <span class="flex items-center gap-2">
                                        <CarStatusBadge :status="change.from" />
                                        <span aria-hidden="true">→</span>
                                        <span class="sr-only">changed to</span>
                                        <CarStatusBadge :status="change.to" />
                                    </span>
                                    <span class="text-gray-500">
                                        {{ formatDateTime(change.changed_at) }} by {{ change.user ?? 'a deleted user' }}
                                    </span>
                                </li>
                            </ol>
                            <p v-else class="text-sm italic text-gray-500">
                                No status changes yet.
                            </p>
                        </section>
                    </template>

                    <template #notes>
                        <section class="space-y-6 bg-white p-6 shadow-sm sm:rounded-lg">
                            <div>
                                <h3 class="text-sm font-medium text-gray-900">Description</h3>
                                <p v-if="car.description" class="mt-1 text-gray-700">
                                    {{ car.description }}
                                </p>
                                <p v-else class="mt-1 text-sm italic text-gray-500">
                                    No description provided.
                                </p>
                            </div>

                            <div v-if="car.internal_notes" class="rounded-md bg-amber-50 p-4">
                                <h3 class="text-sm font-medium text-amber-800">Internal notes</h3>
                                <p class="mt-1 whitespace-pre-line text-sm text-amber-700">{{ car.internal_notes }}</p>
                            </div>
                            <p v-else class="text-sm italic text-gray-500">
                                No internal notes.
                            </p>
                        </section>
                    </template>
                </Tabs>
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
