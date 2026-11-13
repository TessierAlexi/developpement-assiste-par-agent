<script setup>
import { computed } from 'vue';

const props = defineProps({
    status: {
        type: String,
        required: true,
    },
});

const appearances = {
    available: { label: 'Available', classes: 'bg-green-100 text-green-800' },
    on_hold: { label: 'On hold', classes: 'bg-amber-100 text-amber-800' },
    sold: { label: 'Sold', classes: 'bg-gray-200 text-gray-700' },
};

const appearance = computed(() => appearances[props.status] ?? { label: props.status, classes: 'bg-gray-100 text-gray-700' });
</script>

<template>
    <span
        class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
        :class="appearance.classes"
    >
        {{ appearance.label }}
    </span>
</template>
