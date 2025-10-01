<script setup>
import {ref, onMounted, onBeforeUnmount, provide } from 'vue'
import {router} from '@inertiajs/vue3'
import LoadingOverlay from '@/Components/LoadingOverlay.vue'
const loadingCustom = ref(false);
provide('loadingCustom', loadingCustom);
const props = defineProps(['default'])

const loading = ref(false)
const start = () => {
    loading.value = true
}
const finish = () => {
    loading.value = false
}

onMounted(() => {
    router.on('start', start)
    router.on('finish', finish)
    router.on('error', finish)
})
onBeforeUnmount(() => {
    router.off('start', start)
    router.off('finish', finish)
    router.off('error', finish)
})
</script>

<template>
    <LoadingOverlay :show="loading || loadingCustom"/>
    <component :is="props.default"/>
</template>
