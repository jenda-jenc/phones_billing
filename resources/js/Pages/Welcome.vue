<script setup>
import { Head, Link } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
    laravelVersion: {
        type: String,
        required: true,
    },
    phpVersion: {
        type: String,
        required: true,
    },
});

function handleImageError() {
    document.getElementById('screenshot-container')?.classList.add('!hidden');
    document.getElementById('docs-card')?.classList.add('!row-span-1');
    document.getElementById('docs-card-content')?.classList.add('!flex-row');
    document.getElementById('background')?.classList.add('!hidden');
}
</script>

<template>
    <Head title="Welcome" />
    <div class="bg-gray-50 text-black/50 dark:bg-black dark:text-white/50">
        <div
            class="relative flex min-h-screen flex-col items-center justify-center selection:bg-[#FF2D20] selection:text-white"
        >
            <div class="relative w-full max-w-2xl px-6 lg:max-w-7xl">
                <header
                    class="grid grid-cols-2 items-center gap-2 py-10 lg:grid-cols-3"
                >
                    <div class="flex lg:col-start-2 lg:justify-center">
                        <img src="img/senat_logo.svg" alt="Logo" />
                    </div>
                    <br />
                    <nav
                        v-if="canLogin"
                        class="flex lg:col-start-2 lg:justify-center"
                    >
                        <primary-button v-if="$page.props.auth.user">
                            <Link
                                :href="route('dashboard')"
                                class="rounded-md px-3 py-2 text-white ring-1 ring-transparent transition hover:text-white/50 focus:outline-none focus-visible:ring-[#FF2D20] dark:text-white dark:hover:text-white/80 dark:focus-visible:ring-white"
                            >
                                Přejít do aplikace
                            </Link>
                        </primary-button>

                        <template v-else>
                            <primary-button>
                                <Link
                                    :href="route('login')"
                                    class="rounded-md px-3 py-2 text-black text-white ring-1 ring-transparent transition hover:text-black/70 focus:outline-none focus-visible:ring-[#FF2D20] dark:text-white dark:hover:text-white/80 dark:focus-visible:ring-white"
                                >
                                    Přihlásit se
                                </Link>
                            </primary-button>
                            &nbsp;
                            <secondary-button
                                v-if="canRegister"
                                :href="route('register')"
                                class="rounded-md px-3 py-2 text-black ring-1 ring-transparent transition hover:text-black/70 focus:outline-none focus-visible:ring-[#FF2D20] dark:text-white dark:hover:text-white/80 dark:focus-visible:ring-white"
                            >
                                <Link
                                    v-if="canRegister"
                                    :href="route('register')"
                                    class="rounded-md px-3 py-2 text-black ring-1 ring-transparent transition hover:text-black/70 focus:outline-none focus-visible:ring-[#FF2D20] dark:text-white dark:hover:text-white/80 dark:focus-visible:ring-white"
                                >
                                    Registrace
                                </Link>
                            </secondary-button>
                        </template>
                    </nav>
                </header>
                <main class="mt-6"></main>

                <footer
                    class="py-16 text-center text-sm text-black dark:text-white/70"
                >
                    Aplikace k vyúčtování telefonů
                    <br />
                    <!--                    Laravel v{{ laravelVersion }} (PHP v{{ phpVersion }})-->
                </footer>
            </div>
        </div>
    </div>
</template>
