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
});

const sections = [
    {
        title: 'Inventory',
        fields: [
            { name: 'stock_number', label: 'Stock number', placeholder: 'e.g. STK-01042' },
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
            { name: 'fuel_type', label: 'Fuel' },
            { name: 'transmission', label: 'Transmission' },
            { name: 'colour', label: 'Colour' },
            { name: 'mileage_km', label: 'Mileage (km)', type: 'number' },
            { name: 'price', label: 'Price (CAD)', type: 'number' },
            { name: 'description', label: 'Description', type: 'textarea' },
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

const initialValue = (field) => {
    const value = props.car[field.name];

    if (field.type === 'checkbox') {
        return Boolean(value);
    }

    return value == null ? '' : String(value);
};

const form = useForm(
    Object.fromEntries(
        sections.flatMap((section) => section.fields).map((field) => [field.name, initialValue(field)]),
    ),
);

const submit = () => {
    form.submit(props.method, props.action, { preserveScroll: true });
};
</script>

<template>
    <form class="space-y-6" @submit.prevent="submit">
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
                    :class="{ 'sm:col-span-2': field.type === 'textarea' || field.type === 'checkbox' }"
                >
                    <label v-if="field.type === 'checkbox'" class="flex items-center">
                        <Checkbox v-model:checked="form[field.name]" />
                        <span class="ms-2 text-sm text-gray-600">{{ field.label }}</span>
                    </label>
                    <template v-else>
                        <InputLabel :for="field.name" :value="field.label" />
                        <textarea
                            v-if="field.type === 'textarea'"
                            :id="field.name"
                            v-model="form[field.name]"
                            rows="4"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        />
                        <TextInput
                            v-else
                            :id="field.name"
                            v-model="form[field.name]"
                            :type="field.type ?? 'text'"
                            :placeholder="field.placeholder"
                            class="mt-1 block w-full"
                        />
                    </template>
                    <InputError :message="form.errors[field.name]" class="mt-2" />
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
