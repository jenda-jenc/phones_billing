<template>
    <div v-if="show" class="loading-overlay">
        <SenatLoaderX :segments="4" :size="300" />
    </div>
</template>
<script setup>
import SenatLoaderX from './SenatLoaderX.vue'
const props = defineProps({ show: Boolean })
</script>
<style scoped>
.loading-overlay {
    position: fixed;
    inset: 0;
    z-index: 1000;
    background: rgba(0,0,0,0.4);
    display: flex;
    justify-content: center;
    align-items: center;
    backdrop-filter: blur(3px);
}
</style>
