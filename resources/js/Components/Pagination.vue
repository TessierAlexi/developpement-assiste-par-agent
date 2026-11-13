<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    links: {
        type: Array,
        required: true,
    },
});
</script>

<template>
    <nav
        v-if="links.length > 3"
        aria-label="Pagination"
        class="flex flex-wrap justify-center gap-1"
    >
        <template v-for="link in links" :key="link.label">
            <Link
                v-if="link.url"
                :href="link.url"
                preserve-scroll
                :aria-current="link.active ? 'page' : undefined"
                class="rounded-md border px-3 py-2 text-sm leading-4 transition"
                :class="
                    link.active
                        ? 'border-indigo-500 bg-indigo-500 text-white'
                        : 'border-gray-300 bg-white text-gray-700 hover:bg-gray-50'
                "
            >
                <span v-html="link.label" />
            </Link>
            <span
                v-else
                class="rounded-md border border-gray-200 px-3 py-2 text-sm leading-4 text-gray-400"
                v-html="link.label"
            />
        </template>
    </nav>
</template>
