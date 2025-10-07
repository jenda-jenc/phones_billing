<script setup>
defineProps({
    alt: {
        type: String,
        default: 'Logo Senátu Parlamentu České republiky',
    },
});
</script>

<template>
    <img
        src="/img/senat_logo.svg"
        :alt="alt"
        class="h-auto max-w-full"
        loading="lazy"
    />
</template>
