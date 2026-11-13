<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    car: {
        type: Object,
        default: () => ({}),
    },
    method: {
        type: String,
        required: true,
    },
    action: {
        type: String,
        required: true,
    },
    cancelUrl: {
        type: String,
        required: true,
    },
    submitLabel: {
        type: String,
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

const sections = [
    {
        title: 'Inventory',
        fields: [
            { name: 'stock_number', label: 'Stock number', placeholder: 'e.g. STK-01042' },
            { name: 'vin', label: 'VIN', placeholder: '17 characters', maxlength: 17, class: 'uppercase placeholder:normal-case' },
            { name: 'status', label: 'Status', type: 'select', options: props.statuses, required: true },
            { name: 'received_on', label: 'Received on', type: 'date' },
        ],
    },
    {
        title: 'Vehicle',
        fields: [
            { name: 'year', label: 'Year', type: 'number' },
            { name: 'make', label: 'Make' },
            { name: 'model', label: 'Model' },
            { name: 'trim', label: 'Trim' },
            { name: 'body_type', label: 'Body type' },
            { name: 'mileage_km', label: 'Mileage (km)', type: 'number', min: 0 },
            { name: 'doors', label: 'Doors', type: 'number', min: 2, max: 5 },
            { name: 'seats', label: 'Seats', type: 'number', min: 1, max: 15 },
            { name: 'colour', label: 'Exterior colour' },
            { name: 'interior_colour', label: 'Interior colour' },
        ],
    },
    {
        title: 'Powertrain',
        fields: [
            { name: 'engine', label: 'Engine', placeholder: 'e.g. 2.0L I4 Turbo' },
            { name: 'engine_displacement_l', label: 'Displacement (L)', type: 'number', min: 0.5, max: 10, step: 0.1 },
            { name: 'cylinders', label: 'Cylinders', type: 'number', min: 2, max: 16 },
            { name: 'transmission', label: 'Transmission' },
            { name: 'drivetrain', label: 'Drivetrain', type: 'select', options: props.drivetrains },
            { name: 'fuel_type', label: 'Fuel' },
            { name: 'city_l_per_100km', label: 'City consumption (L/100 km)', type: 'number', min: 1, max: 40, step: 0.1 },
            { name: 'highway_l_per_100km', label: 'Highway consumption (L/100 km)', type: 'number', min: 1, max: 40, step: 0.1 },
        ],
    },
    {
        title: 'Equipment',
        fields: [
            { name: 'equipment', label: 'Equipment', type: 'checkbox-groups', groups: props.equipmentCatalogue },
        ],
    },
    {
        title: 'Pricing',
        fields: [
            { name: 'price', label: 'Asking price (CAD)', type: 'number', min: 0 },
            { name: 'cost_price', label: 'Cost price (CAD)', type: 'number', min: 0 },
        ],
    },
    {
        title: 'Description and notes',
        fields: [
            { name: 'description', label: 'Description', type: 'textarea' },
            { name: 'internal_notes', label: 'Internal notes (staff only)', type: 'textarea' },
        ],
    },
    {
        title: 'Photo',
        fields: [
            { name: 'photo_url', label: 'Photo URL', type: 'url' },
            { name: 'photo_source_url', label: 'Photo source URL', type: 'url' },
            { name: 'photo_author', label: 'Photo author' },
            { name: 'photo_licence', label: 'Photo licence' },
            { name: 'photo_licence_url', label: 'Photo licence URL', type: 'url' },
            { name: 'photo_attribution_required', label: 'The photo licence requires an attribution', type: 'checkbox' },
        ],
    },
];

const nativeControlClass = 'rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500';

const initialValue = (field) => {
    const value = props.car[field.name];

    if (field.type === 'checkbox') {
        return Boolean(value);
    }

    if (field.type === 'checkbox-groups') {
        return [...(value ?? [])];
    }

    return value == null ? '' : String(value);
};

const form = useForm(
    Object.fromEntries(
        sections.flatMap((section) => section.fields).map((field) => [field.name, initialValue(field)]),
    ),
);

const isFullWidth = (field) => ['textarea', 'checkbox', 'checkbox-groups'].includes(field.type);

const errorFor = (field) =>
    form.errors[field.name] ??
    Object.entries(form.errors).find(([key]) => key.startsWith(`${field.name}.`))?.[1];

const submit = () => {
    form.submit(props.method, props.action, { preserveScroll: true });
};
</script>

<template>
    <form class="space-y-6" novalidate @submit.prevent="submit">
        <section
            v-for="section in sections"
            :key="section.title"
            class="bg-white p-6 shadow-sm sm:rounded-lg"
        >
            <h3 class="text-lg font-medium text-gray-900">{{ section.title }}</h3>

            <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div
                    v-for="field in section.fields"
                    :key="field.name"
                    :class="{ 'sm:col-span-2': isFullWidth(field) }"
                >
                    <label v-if="field.type === 'checkbox'" class="flex items-center">
                        <Checkbox v-model:checked="form[field.name]" />
                        <span class="ms-2 text-sm text-gray-600">{{ field.label }}</span>
                    </label>
                    <div
                        v-else-if="field.type === 'checkbox-groups'"
                        class="grid grid-cols-1 gap-x-6 gap-y-6 sm:grid-cols-2 lg:grid-cols-3"
                    >
                        <fieldset v-for="group in field.groups" :key="group.label">
                            <legend class="text-sm font-medium text-gray-700">{{ group.label }}</legend>
                            <label
                                v-for="option in group.options"
                                :key="option.value"
                                class="mt-2 flex items-center"
                            >
                                <Checkbox v-model:checked="form[field.name]" :value="option.value" />
                                <span class="ms-2 text-sm text-gray-600">{{ option.label }}</span>
                            </label>
                        </fieldset>
                    </div>
                    <template v-else>
                        <InputLabel :for="field.name" :value="field.label" />
                        <textarea
                            v-if="field.type === 'textarea'"
                            :id="field.name"
                            v-model="form[field.name]"
                            rows="4"
                            class="mt-1 block w-full"
                            :class="nativeControlClass"
                        />
                        <select
                            v-else-if="field.type === 'select'"
                            :id="field.name"
                            v-model="form[field.name]"
                            class="mt-1 block w-full"
                            :class="nativeControlClass"
                        >
                            <option v-if="!field.required" value="">Not specified</option>
                            <option v-for="option in field.options" :key="option.value" :value="option.value">
                                {{ option.label }}
                            </option>
                        </select>
                        <TextInput
                            v-else
                            :id="field.name"
                            v-model="form[field.name]"
                            :type="field.type ?? 'text'"
                            :placeholder="field.placeholder"
                            :min="field.min"
                            :max="field.max"
                            :step="field.step"
                            :maxlength="field.maxlength"
                            class="mt-1 block w-full"
                            :class="field.class"
                        />
                    </template>
                    <InputError :message="errorFor(field)" class="mt-2" />
                </div>
            </div>
        </section>

        <div class="flex items-center justify-end gap-4 px-4 sm:px-0">
            <Link :href="cancelUrl" class="text-sm text-gray-600 underline hover:text-gray-900">
                Cancel
            </Link>
            <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                {{ submitLabel }}
            </PrimaryButton>
        </div>
    </form>
</template>
