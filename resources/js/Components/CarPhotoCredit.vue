<script setup>
defineProps({
    car: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <figcaption class="px-4 py-3 text-xs text-gray-500">
        Representative photo of the model by
        <a :href="car.photo_source_url" class="underline hover:text-gray-700" target="_blank" rel="noopener">{{ car.photo_author }}</a>,
        licensed under
        <a :href="car.photo_licence_url" class="underline hover:text-gray-700" target="_blank" rel="noopener license">{{ car.photo_licence }}</a>,
        via Wikimedia Commons.
    </figcaption>
</template>
