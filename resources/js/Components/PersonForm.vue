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

            <!-- Telefonní čísla -->
            <div class="mb-4">
                <label class="mb-2 block font-semibold text-gray-700">
                    Telefonní čísla
                </label>
                <div
                    v-for="(phone, index) in formData.phones"
                    :key="`phone-${index}`"
                    class="mb-2 flex items-start gap-2"
                >
                    <input
                        :id="`phone-${index}`"
                        type="tel"
                        v-model="formData.phones[index]"
                        class="w-full rounded-md border border-gray-300 px-3 py-2 text-gray-800 transition focus:border-blue-500 focus:ring focus:ring-blue-200"
                        placeholder="Zadejte telefonní číslo"
                    />
                    <button
                        type="button"
                        class="rounded bg-red-500 px-3 py-2 text-sm font-semibold text-white transition hover:bg-red-600"
                        @click="removePhone(index)"
                    >
                        Odebrat
                    </button>
                </div>
                <button
                    type="button"
                    class="rounded bg-green-500 px-4 py-2 text-sm font-semibold text-white transition hover:bg-green-600"
                    @click="addPhone"
                >
                    Přidat číslo
                </button>
                <p v-if="errors.phones" class="mt-2 text-sm text-red-500">
                    {{ errors.phones }}
                </p>
                <template v-for="(phone, index) in formData.phones" :key="`error-${index}`">
                    <p
                        v-if="errors[`phones.${index}`]"
                        class="mt-1 text-sm text-red-500"
                    >
                        {{ errors[`phones.${index}`] }}
                    </p>
                </template>
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
            phones: [''],
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

const formData = reactive({
    id: null,
    name: '',
    phones: [''],
    department: '',
    limit: 450,
});

const normalizePhones = (phones) => {
    if (!Array.isArray(phones) || phones.length === 0) {
        return [''];
    }

    const values = phones.map((entry) => {
        if (typeof entry === 'object' && entry !== null) {
            return entry.phone ?? '';
        }

        return typeof entry === 'string' ? entry : '';
    });

    return values.length ? values : [''];
};

const setFormData = (data) => {
    formData.id = data?.id ?? null;
    formData.name = data?.name ?? '';
    formData.department = data?.department ?? '';
    formData.limit = data?.limit ?? 450;

    const phones = normalizePhones(data?.phones ?? ['']);

    formData.phones.splice(0, formData.phones.length, ...phones);

    if (formData.phones.length === 0) {
        formData.phones.push('');
    }
};

setFormData(props.initialData);

watch(
    () => props.initialData,
    (newValue) => {
        setFormData(newValue ?? {});
    },
    { deep: true },
);

const addPhone = () => {
    formData.phones.push('');
};

const removePhone = (index) => {
    if (formData.phones.length === 1) {
        formData.phones.splice(0, 1, '');
        return;
    }

    formData.phones.splice(index, 1);
};

const handleSubmit = () => {
    const payload = {
        id: formData.id,
        name: formData.name,
        department: formData.department,
        limit: formData.limit,
        phones: formData.phones
            .map((phone) => (typeof phone === 'string' ? phone.trim() : ''))
            .filter((phone) => phone !== ''),
    };

    emit('submit', payload);
};
</script>
