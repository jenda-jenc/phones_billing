<template>
    <div class="rounded-xl bg-gray-50 p-6">
        <form @submit.prevent="handleSubmit">
            <!-- Jméno -->
            <div class="mb-4">
                <label for="name" class="mb-2 block font-semibold text-gray-700"
                    >Jméno</label
                >
                <input
                    id="name"
                    type="text"
                    v-model="formData.name"
                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-gray-800 transition focus:border-blue-500 focus:ring focus:ring-blue-200"
                    placeholder="Zadejte jméno"
                />
                <p v-if="errors.name" class="mt-1 text-sm text-red-500">
                    {{ errors.name }}
                </p>
            </div>

            <!-- Telefonní číslo -->
            <div class="mb-4">
                <label
                    for="phone"
                    class="mb-2 block font-semibold text-gray-700"
                    >Telefonní číslo</label
                >
                <input
                    id="phone"
                    type="tel"
                    v-model="formData.phone"
                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-gray-800 transition focus:border-blue-500 focus:ring focus:ring-blue-200"
                    placeholder="Zadejte telefonní číslo"
                />
                <p v-if="errors.phone" class="mt-1 text-sm text-red-500">
                    {{ errors.phone }}
                </p>
            </div>

            <!-- Pracovní útvar -->
            <div class="mb-4">
                <label
                    for="department"
                    class="mb-2 block font-semibold text-gray-700"
                    >Pracovní útvar</label
                >
                <input
                    id="department"
                    type="text"
                    v-model="formData.department"
                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-gray-800 transition focus:border-blue-500 focus:ring focus:ring-blue-200"
                    placeholder="Zadejte pracovní útvar"
                />
                <p v-if="errors.department" class="mt-1 text-sm text-red-500">
                    {{ errors.department }}
                </p>
            </div>

            <!-- Limit -->
            <div class="mb-4">
                <label
                    for="limit"
                    class="mb-2 block font-semibold text-gray-700"
                    >Limit</label
                >
                <input
                    id="limit"
                    type="number"
                    step="0.1"
                    v-model="formData.limit"
                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-gray-800 transition focus:border-blue-500 focus:ring focus:ring-blue-200"
                    placeholder="Zadejte limit"
                />
                <p v-if="errors.limit" class="mt-1 text-sm text-red-500">
                    {{ errors.limit }}
                </p>
            </div>

            <!-- Tlačítko pro odeslání -->
            <div class="mt-6">
                <button
                    type="submit"
                    class="w-full rounded-md bg-blue-600 px-4 py-2 text-white shadow transition hover:bg-blue-700"
                >
                    {{ isEditing ? 'Upravit Osobu' : 'Uložit Osobu' }}
                </button>
            </div>
        </form>
    </div>
</template>

<script setup>
import { reactive, watch } from 'vue';

const props = defineProps({
    initialData: {
        type: Object,
        default: () => ({
            name: '',
            phone: '',
            department: '',
            limit: 450,
        }),
    },
    errors: {
        type: Object,
        default: () => ({}),
    },
    isEditing: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['submit']);

const formData = reactive({ ...props.initialData });

watch(
    () => props.initialData,
    (newValue) => {
        Object.assign(formData, newValue);
    },
    { deep: true },
);

const handleSubmit = () => {
    emit('submit', { ...formData });
};
</script>
