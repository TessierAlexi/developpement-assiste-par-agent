<script setup>
import { tabIndexAfterKey } from '@/tabs';
import { computed, useId } from 'vue';

const props = defineProps({
    tabs: {
        type: Array,
        required: true,
    },
    modelValue: {
        type: String,
        default: null,
    },
    label: {
        type: String,
        required: true,
    },
});

const emit = defineEmits(['update:modelValue']);

const id = useId();
const tabElements = [];

const activeValue = computed(() =>
    props.tabs.some((tab) => tab.value === props.modelValue) ? props.modelValue : props.tabs[0]?.value,
);

const tabId = (value) => `${id}-tab-${value}`;
const panelId = (value) => `${id}-panel-${value}`;

const select = (value) => {
    if (value !== activeValue.value) {
        emit('update:modelValue', value);
    }
};

const moveWithKeyboard = (event, index) => {
    const nextIndex = tabIndexAfterKey(event.key, index, props.tabs.length);

    if (nextIndex === null) {
        return;
    }

    event.preventDefault();
    select(props.tabs[nextIndex].value);
    tabElements[nextIndex]?.focus();
};
</script>

<template>
    <div>
        <div
            role="tablist"
            :aria-label="label"
            class="flex gap-6 overflow-x-auto border-b border-gray-200 px-4 sm:px-0"
        >
            <button
                v-for="(tab, index) in tabs"
                :id="tabId(tab.value)"
                :key="tab.value"
                :ref="(element) => (tabElements[index] = element)"
                type="button"
                role="tab"
                :aria-selected="tab.value === activeValue"
                :aria-controls="panelId(tab.value)"
                :tabindex="tab.value === activeValue ? 0 : -1"
                class="-mb-px whitespace-nowrap border-b-2 px-1 py-3 text-sm font-medium transition duration-150 ease-in-out focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-indigo-500"
                :class="
                    tab.value === activeValue
                        ? 'border-indigo-400 text-gray-900'
                        : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'
                "
                @click="select(tab.value)"
                @keydown="moveWithKeyboard($event, index)"
            >
                {{ tab.label }}
            </button>
        </div>

        <div
            v-for="tab in tabs"
            v-show="tab.value === activeValue"
            :id="panelId(tab.value)"
            :key="tab.value"
            role="tabpanel"
            :aria-labelledby="tabId(tab.value)"
            tabindex="0"
            class="mt-6 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500"
        >
            <slot :name="tab.value" />
        </div>
    </div>
</template>
