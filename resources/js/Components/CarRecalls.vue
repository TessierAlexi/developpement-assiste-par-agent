<script setup>
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { formatText } from '@/formatters';
import { noRecallMessage, recallCountLabel, recallErrorMessage } from '@/recalls';
import { onMounted, ref } from 'vue';

const props = defineProps({
    car: {
        type: Object,
        required: true,
    },
});

const recallDetails = [
    ['summary', 'Summary'],
    ['consequence', 'Consequence'],
    ['remedy', 'Remedy'],
];

const recalls = ref(null);
const error = ref(null);
const isLoading = ref(false);

let latestRequest = 0;

const load = async () => {
    const request = ++latestRequest;

    isLoading.value = true;
    error.value = null;

    try {
        const response = await fetch(route('cars.recalls', props.car.id), {
            headers: { Accept: 'application/json' },
        });
        const body = await response.json().catch(() => null);

        if (request !== latestRequest) {
            return;
        }

        if (response.ok && Array.isArray(body?.recalls)) {
            recalls.value = body.recalls;
        } else {
            recalls.value = null;
            error.value = recallErrorMessage(body);
        }
    } catch {
        if (request === latestRequest) {
            recalls.value = null;
            error.value = recallErrorMessage(null);
        }
    } finally {
        if (request === latestRequest) {
            isLoading.value = false;
        }
    }
};

onMounted(load);
</script>

<template>
    <section class="bg-white p-6 shadow-sm sm:rounded-lg" aria-labelledby="recalls-heading">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h3 id="recalls-heading" class="text-lg font-medium text-gray-900">Safety recalls</h3>
                <p class="mt-1 text-sm text-gray-500">
                    Listed by NHTSA for every {{ car.year }} {{ car.make }} {{ car.model }}, not for this VIN. A
                    recall shown here may already have been repaired on this car.
                </p>
            </div>
            <SecondaryButton :class="{ 'opacity-25': isLoading }" :disabled="isLoading" @click="load">
                Check recalls
            </SecondaryButton>
        </div>

        <div class="mt-4" aria-live="polite">
            <div v-if="isLoading" class="animate-pulse space-y-3">
                <span class="sr-only">Checking recalls…</span>
                <div class="h-4 w-1/3 rounded bg-gray-200" />
                <div class="h-4 w-3/4 rounded bg-gray-200" />
                <div class="h-4 w-2/3 rounded bg-gray-200" />
            </div>

            <p v-else-if="error" class="rounded-md bg-red-50 p-4 text-sm text-red-700" role="alert">
                {{ error }}
            </p>

            <p v-else-if="recalls && recalls.length === 0" class="rounded-md bg-green-50 p-4 text-sm text-green-800">
                {{ noRecallMessage(car) }}
            </p>

            <div v-else-if="recalls">
                <p class="text-sm font-medium text-amber-800">{{ recallCountLabel(recalls.length) }}</p>
                <ul class="mt-2 divide-y divide-gray-100">
                    <li v-for="recall in recalls" :key="recall.campaign_number" class="py-4">
                        <p class="text-sm font-medium text-gray-900">
                            Campaign {{ recall.campaign_number }}
                            <span class="font-normal text-gray-500">· {{ formatText(recall.component) }}</span>
                        </p>
                        <dl class="mt-2 space-y-2 text-sm">
                            <div v-for="[name, label] in recallDetails" :key="name">
                                <dt class="font-medium text-gray-500">{{ label }}</dt>
                                <dd class="text-gray-700">{{ formatText(recall[name]) }}</dd>
                            </div>
                        </dl>
                    </li>
                </ul>
            </div>
        </div>
    </section>
</template>
