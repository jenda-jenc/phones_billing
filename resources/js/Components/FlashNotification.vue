<template>
    <transition name="slide">
        <div
            v-if="visible && message"
            class="flash-notification fixed bottom-4 right-4 flex items-center rounded border-l-4 border-green-500 bg-green-100 p-4 text-green-700 shadow-lg"
            role="alert"
        >
            <div class="flex-1">
                {{ message }}
            </div>
            <button
                @click="close"
                class="ml-4 text-green-700 hover:text-green-900"
                aria-label="Zavřít"
            >
                <svg
                    class="h-6 w-6"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M6 18L18 6M6 6l12 12"
                    />
                </svg>
            </button>
        </div>
    </transition>
</template>

<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
    message: {
        type: String,
        default: '',
    },
    duration: {
        type: Number,
        default: 5000,
    },
});

const visible = ref(false);

const close = () => {
    visible.value = false;
};

// **TADY sleduj změnu props.message!**
watch(
    () => props.message,
    (newMessage) => {
        if (newMessage) {
            visible.value = true;
            setTimeout(() => {
                visible.value = false;
            }, props.duration);
        }
    },
    { immediate: true },
);
</script>

<style scoped>
.slide-enter-active,
.slide-leave-active {
    transition:
        transform 0.5s ease,
        opacity 0.5s ease;
}
.slide-enter-from {
    transform: translateX(100%);
    opacity: 0;
}
.slide-enter-to {
    transform: translateX(0);
    opacity: 1;
}
.slide-leave-from {
    transform: translateX(0);
    opacity: 1;
}
.slide-leave-to {
    transform: translateX(100%);
    opacity: 0;
}
</style>
