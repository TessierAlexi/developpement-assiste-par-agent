<script setup>
import { gaugeFraction } from '@/gauge';
import { computed } from 'vue';

const props = defineProps({
    label: {
        type: String,
        required: true,
    },
    value: {
        type: Number,
        default: null,
    },
    target: {
        type: Number,
        required: true,
    },
    display: {
        type: String,
        required: true,
    },
    caption: {
        type: String,
        default: '',
    },
});

const ARC = 'M 10 100 A 90 90 0 0 1 190 100';

const filledPercent = computed(() => gaugeFraction(props.value, props.target) * 100);

const description = computed(() => [`${props.label}: ${props.display}`, props.caption].filter(Boolean).join(' — '));
</script>

<template>
    <figure class="flex flex-col items-center bg-white p-6 shadow-sm sm:rounded-lg">
        <figcaption class="text-sm font-medium text-gray-500">{{ label }}</figcaption>

        <svg
            class="mt-3 w-full max-w-[14rem]"
            viewBox="0 0 200 110"
            xmlns="http://www.w3.org/2000/svg"
            role="img"
            :aria-label="description"
        >
            <title>{{ description }}</title>
            <path
                :d="ARC"
                fill="none"
                class="stroke-gray-200"
                stroke-width="14"
                stroke-linecap="round"
            />
            <path
                v-if="filledPercent > 0"
                :d="ARC"
                fill="none"
                class="stroke-indigo-600"
                stroke-width="14"
                stroke-linecap="round"
                pathLength="100"
                :stroke-dasharray="`${filledPercent} 100`"
            />
            <text
                x="100"
                y="96"
                text-anchor="middle"
                class="fill-gray-900 text-3xl font-semibold"
            >
                {{ display }}
            </text>
        </svg>

        <p class="mt-2 min-h-5 text-center text-sm text-gray-500">{{ caption }}</p>
    </figure>
</template>
