<template>
    <div
        class="loader-container"
        :style="{ width: size + 'px', height: size + 'px' }"
    >
        <svg
            class="progress-circle"
            viewBox="0 0 260 260"
            :style="{ width: svgWidth + 'px', height: svgHeight + 'px' }"
        >
            <circle
                cx="130"
                cy="130"
                r="125"
                :stroke-dasharray="strokeDasharray"
                :stroke-dashoffset="strokeDashoffset"
            ></circle>
        </svg>
        <img :src="rotatingImg" alt="Rotující střed" class="rotating-img" />
        <img :src="staticImg" alt="Statický lv" class="static-img" />
    </div>
</template>

<script setup>
const props = defineProps({
    size: { type: Number, default: 300 },
    svgWidth: { type: Number, default: 277 },
    svgHeight: { type: Number, default: 277 },
    rotatingImg: {
        type: String,
        default:
            'https://www.senat.cz/informace/pro_media/logo/images/symbol_SENAT_RGB.svg',
    },
    staticImg: {
        type: String,
        default:
            'https://www.senat.cz/informace/pro_media/logo/images/logo_SENAT_CZ_vertical_RGB.svg',
    },
    strokeDasharray: { type: String, default: '98.175 98.175' }, // HTML hodnota
    strokeDashoffset: { type: Number, default: 796 },
});
</script>

<style scoped>
.loader-container {
    position: relative;
    width: 300px;
    height: 300px;
    overflow: hidden;
}
.loader-container img {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
}
.loader-container .rotating-img {
    width: 50px;
    height: 50px;
    left: 41.62%;
    top: 21.18%;
    transform: translate(-50%, -50%) rotate(0deg);
    animation: rotate 1.3s linear infinite;
    transform-origin: 50% 50%;
    position: absolute;
    /* DŮLEŽITÉ: musí být absolutní kvůli přesné pozici */
}
.static-img {
    mask-image: radial-gradient(
        circle 25.25px at 49.94% 29.5%,
        transparent 99%,
        black 100%
    );
    -webkit-mask-image: radial-gradient(
        circle 25.25px at 49.94% 29.5%,
        transparent 99%,
        black 100%
    );
}
.progress-circle {
    position: absolute;
    width: 276px;
    height: 276px;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
}
.progress-circle circle {
    fill: none;
    stroke-width: 9;
    stroke: #003478;
    stroke-dasharray: 130.9 261.8;
    stroke-dashoffset: 796;
    animation: progress-loop 2.6s linear infinite;
    transform-origin: 50% 50%;
    transform: rotate(-90deg);
}
@keyframes rotate {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}
@keyframes progress-loop {
    0% {
        stroke-dashoffset: 0;
    }
    100% {
        stroke-dashoffset: -785.4;
    }
}
</style>
