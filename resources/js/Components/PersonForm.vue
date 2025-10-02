<template>
    <div class="rounded-xl bg-gray-50 p-6">
        <form @submit.prevent="handleSubmit">
            <!-- Jméno -->
            <div class="mb-4">
                <label for="name" class="mb-2 block font-semibold text-gray-700">
                    Jméno
                </label>
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
                    class="mb-3 rounded-lg border border-gray-200 bg-white p-3"
                >
                    <div class="grid gap-3 sm:grid-cols-[minmax(0,1fr)_minmax(0,220px)_auto] sm:items-end">
                        <div class="flex flex-col">
                            <label
                                :for="`phone-number-${index}`"
                                class="mb-1 text-sm font-semibold text-gray-600"
                            >
                                Telefonní číslo
                            </label>
                            <input
                                :id="`phone-number-${index}`"
                                type="tel"
                                v-model="formData.phones[index].phone"
                                class="w-full rounded-md border border-gray-300 px-3 py-2 text-gray-800 transition focus:border-blue-500 focus:ring focus:ring-blue-200"
                                placeholder="Zadejte telefonní číslo"
                            />
                            <p
                                v-if="phoneError(index, 'phone')"
                                class="mt-1 text-sm text-red-500"
                            >
                                {{ phoneError(index, 'phone') }}
                            </p>
                        </div>

                        <div class="flex flex-col">
                            <label
                                :for="`phone-limit-${index}`"
                                class="mb-1 text-sm font-semibold text-gray-600"
                            >
                                Limit
                            </label>
                            <input
                                :id="`phone-limit-${index}`"
                                type="number"
                                step="0.01"
                                min="0"
                                v-model="formData.phones[index].limit"
                                class="w-full rounded-md border border-gray-300 px-3 py-2 text-gray-800 transition focus:border-blue-500 focus:ring focus:ring-blue-200"
                                placeholder="Zadejte limit"
                            />
                            <p
                                v-if="phoneError(index, 'limit')"
                                class="mt-1 text-sm text-red-500"
                            >
                                {{ phoneError(index, 'limit') }}
                            </p>
                        </div>

                        <div class="flex justify-end">
                            <button
                                type="button"
                                class="rounded bg-red-500 px-3 py-2 text-sm font-semibold text-white transition hover:bg-red-600"
                                @click="removePhone(index)"
                            >
                                Odebrat
                            </button>
                        </div>
                    </div>
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
            </div>

            <!-- Pracovní útvar -->
            <div class="mb-4">
                <label
                    for="department"
                    class="mb-2 block font-semibold text-gray-700"
                >
                    Pracovní útvar
                </label>
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
import { reactive, watch, computed } from 'vue';

const DEFAULT_PHONE_LIMIT = '450';

const props = defineProps({
    initialData: {
        type: Object,
        default: () => ({
            name: '',
            phones: [
                {
                    phone: '',
                    limit: DEFAULT_PHONE_LIMIT,
                },
            ],
            department: '',
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

const errors = computed(() => props.errors ?? {});

const emit = defineEmits(['submit']);

const formData = reactive({
    id: null,
    name: '',
    phones: [createPhoneEntry('', DEFAULT_PHONE_LIMIT, DEFAULT_PHONE_LIMIT)],
    department: '',
});

const formatLimit = (value, fallback = '') => {
    if (value === null || value === undefined || value === '') {
        return fallback;
    }

    if (typeof value === 'number') {
        return Number.isFinite(value) ? String(value) : fallback;
    }

    if (typeof value === 'string') {
        const normalized = value.replace(',', '.');
        return Number.isFinite(Number(normalized)) ? normalized : fallback;
    }

    return fallback;
};

const createPhoneEntry = (phone = '', limit = '', fallback = '') => ({
    phone: typeof phone === 'string' ? phone : '',
    limit: formatLimit(limit, fallback),
});

const normalizePhones = (phones) => {
    if (!Array.isArray(phones) || phones.length === 0) {
        return [createPhoneEntry('', DEFAULT_PHONE_LIMIT, DEFAULT_PHONE_LIMIT)];
    }

    const normalized = phones
        .map((entry) => {
            if (entry && typeof entry === 'object') {
                return createPhoneEntry(entry.phone ?? '', entry.limit ?? '', '');
            }

            if (typeof entry === 'string') {
                return createPhoneEntry(entry, '', '');
            }

            return createPhoneEntry('', '', '');
        })
        .filter((entry) => entry.phone !== '');

    return normalized.length
        ? normalized
        : [createPhoneEntry('', DEFAULT_PHONE_LIMIT, DEFAULT_PHONE_LIMIT)];
};

const setFormData = (data) => {
    formData.id = data?.id ?? null;
    formData.name = data?.name ?? '';
    formData.department = data?.department ?? '';

    const phones = normalizePhones(data?.phones ?? []);
    formData.phones.splice(0, formData.phones.length, ...phones);
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
    formData.phones.push(createPhoneEntry('', DEFAULT_PHONE_LIMIT, DEFAULT_PHONE_LIMIT));
};

const removePhone = (index) => {
    if (formData.phones.length === 1) {
        formData.phones.splice(
            0,
            1,
            createPhoneEntry('', DEFAULT_PHONE_LIMIT, DEFAULT_PHONE_LIMIT),
        );
        return;
    }

    formData.phones.splice(index, 1);
};

const parseLimit = (value) => {
    if (value === null || value === undefined || value === '') {
        return null;
    }

    const normalized = typeof value === 'string' ? value.replace(',', '.') : value;
    const number = Number(normalized);

    if (!Number.isFinite(number)) {
        return null;
    }

    return Math.round(number * 100) / 100;
};

const phoneError = (index, field) => {
    const currentErrors = errors.value;
    const baseKey = `phones.${index}`;

    return (
        currentErrors[`${baseKey}.${field}`] ??
        currentErrors[baseKey] ??
        null
    );
};

const handleSubmit = () => {
    const payload = {
        id: formData.id,
        name: formData.name,
        department: formData.department,
        phones: formData.phones
            .map((entry) => ({
                phone: typeof entry.phone === 'string' ? entry.phone.trim() : '',
                limit: parseLimit(entry.limit),
            }))
            .filter((entry) => entry.phone !== ''),
    };

    emit('submit', payload);
};
</script>
