<template>
    <div class="p-6 bg-gray-50 rounded-xl">
        <form @submit.prevent="handleSubmit">
            <!-- Jméno -->
            <div class="mb-4">
                <label for="name" class="block text-gray-700 font-semibold mb-2">Jméno</label>
                <input
                    id="name"
                    type="text"
                    v-model="formData.name"
                    class="w-full border border-gray-300 rounded-md focus:ring focus:ring-blue-200 focus:border-blue-500 px-3 py-2 text-gray-800 transition"
                    placeholder="Zadejte jméno"
                />
                <p v-if="errors.name" class="text-red-500 text-sm mt-1">{{ errors.name }}</p>
            </div>

            <!-- Telefonní číslo -->
            <div class="mb-4">
                <label for="phone" class="block text-gray-700 font-semibold mb-2">Telefonní číslo</label>
                <input
                    id="phone"
                    type="tel"
                    v-model="formData.phone"
                    class="w-full border border-gray-300 rounded-md focus:ring focus:ring-blue-200 focus:border-blue-500 px-3 py-2 text-gray-800 transition"
                    placeholder="Zadejte telefonní číslo"
                />
                <p v-if="errors.phone" class="text-red-500 text-sm mt-1">{{ errors.phone }}</p>
            </div>

            <!-- Pracovní útvar -->
            <div class="mb-4">
                <label for="department" class="block text-gray-700 font-semibold mb-2">Pracovní útvar</label>
                <input
                    id="department"
                    type="text"
                    v-model="formData.department"
                    class="w-full border border-gray-300 rounded-md focus:ring focus:ring-blue-200 focus:border-blue-500 px-3 py-2 text-gray-800 transition"
                    placeholder="Zadejte pracovní útvar"
                />
                <p v-if="errors.department" class="text-red-500 text-sm mt-1">{{ errors.department }}</p>
            </div>

            <!-- Limit -->
            <div class="mb-4">
                <label for="limit" class="block text-gray-700 font-semibold mb-2">Limit</label>
                <input
                    id="limit"
                    type="number"
                    step="0.1"
                    v-model="formData.limit"
                    class="w-full border border-gray-300 rounded-md focus:ring focus:ring-blue-200 focus:border-blue-500 px-3 py-2 text-gray-800 transition"
                    placeholder="Zadejte limit"
                />
                <p v-if="errors.limit" class="text-red-500 text-sm mt-1">{{ errors.limit }}</p>
            </div>

            <!-- Tlačítko pro odeslání -->
            <div class="mt-6">
                <button
                    type="submit"
                    class="w-full bg-blue-600 text-white px-4 py-2 rounded-md shadow hover:bg-blue-700 transition"
                >
                    {{ isEditing ? 'Upravit Osobu' : 'Uložit Osobu' }}
                </button>
            </div>
        </form>
    </div>
</template>

<script setup>
import {reactive, watch} from 'vue';

const props = defineProps({
    initialData: {
        type: Object,
        default: () => ({
            name: '',
            phone: '',
            department: '',
            limit: 450
        })
    },
    errors: {
        type: Object,
        default: () => ({})
    },
    isEditing: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['submit']);

const formData = reactive({...props.initialData});

watch(
    () => props.initialData,
    (newValue) => {
        Object.assign(formData, newValue);
    },
    {deep: true}
);

const handleSubmit = () => {
    emit('submit', {...formData});
};
</script>
