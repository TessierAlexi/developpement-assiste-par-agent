<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
    src: {
        type: String,
        default: null,
    },
    alt: {
        type: String,
        required: true,
    },
});

const hasFailed = ref(false);

watch(
    () => props.src,
    () => {
        hasFailed.value = false;
    },
);
</script>

<template>
    <div class="overflow-hidden bg-gray-100">
        <img
            v-if="src && !hasFailed"
            :src="src"
            :alt="alt"
            loading="lazy"
            class="h-full w-full object-cover"
            @error="hasFailed = true"
        />
        <div
            v-else
            role="img"
            :aria-label="`${alt} (photo unavailable)`"
            class="flex h-full w-full flex-col items-center justify-center gap-2 text-gray-400"
        >
            <svg
                class="h-12 w-12"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke-width="1.5"
                stroke="currentColor"
                aria-hidden="true"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M3 13l2-5.5A2 2 0 0 1 6.9 6h10.2a2 2 0 0 1 1.9 1.5L21 13m-18 0h18m-18 0v4a1 1 0 0 0 1 1h1m14-5v4a1 1 0 0 1-1 1h-1M7 18a1.5 1.5 0 1 0 0-.01M17 18a1.5 1.5 0 1 0 0-.01M5 18h14"
                />
            </svg>
            <span class="text-sm">Photo unavailable</span>
        </div>
    </div>
</template>
