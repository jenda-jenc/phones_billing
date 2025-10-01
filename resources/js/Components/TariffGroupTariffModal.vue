<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import axios from 'axios';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    group: Object,
    tariffs: Object,
});
const emit = defineEmits(['close']);

// --- Přepínání tabů ---
const tab = ref('vyber'); // 'vyber' nebo 'csv'

// --- Standardní přiřazení tarifu ---
const tariffForm = useForm({
    group_id: props.group?.id,
    tariff_id: '',
    action: 'plati_sam',
});
const tariffActions = [
    // { value: 'do_limitu', label: 'Do limitu' },
    { value: 'ignorovat', label: 'Ignorovat' },
    { value: 'plati_sam', label: 'Platí sám' },
];

const submit = () => {
    tariffForm.group_id = props.group.id;
    tariffForm.post(route('groups.attach-tariff'), {
        onSuccess: () => {
            tariffForm.reset();
            emit('close');
        },
    });
};

// --- CSV import ---
const csvFile = ref(null);
const columns = ref([]);
const csvTariffColumn = ref('');
const uniqueTariffs = ref([]);
const uploading = ref(false);

const parseCSVHeader = (file) => {
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.onload = (e) => {
            const arrayBuffer = e.target.result;
            const decoder = new TextDecoder('windows-1250');
            const text = decoder.decode(arrayBuffer);
            const lines = text.split(/\r\n|\n/);
            if (lines.length > 0) {
                const headerLine = lines[0];
                const columns = headerLine.split(';').map((col) => col.trim());
                resolve({ columns, text });
            } else {
                reject(new Error('CSV soubor neobsahuje žádná data.'));
            }
        };
        reader.onerror = (error) => reject(error);
        reader.readAsArrayBuffer(file);
    });
};

const handleCSVFile = async (event) => {
    const file = event.target.files[0];
    if (!file) return;
    csvFile.value = file;
    uploading.value = true;
    try {
        const { columns: foundColumns, text } = await parseCSVHeader(file);
        columns.value = foundColumns;
        window.__csvText = text;
    } finally {
        uploading.value = false;
    }
};

const getUniqueTariffsFromCSV = () => {
    const lines = window.__csvText.split(/\r\n|\n/);
    if (lines.length < 2 || !csvTariffColumn.value) return;
    const colIdx = columns.value.indexOf(csvTariffColumn.value);
    const values = [];
    for (let i = 1; i < lines.length; i++) {
        const cells = lines[i].split(';');
        if (cells.length > colIdx) {
            const val = cells[colIdx]?.trim();
            if (val) values.push(val);
        }
    }
    uniqueTariffs.value = [...new Set(values)];
};

const saveUniqueTariffs = () => {
    if (!uniqueTariffs.value.length) return;
    uploading.value = true;
    axios
        .post(route('tariffs.bulk-store'), {
            names: uniqueTariffs.value,
        })
        .then(() => {
            alert('Služby z CSV byly uloženy.');
            emit('close');
            // Nejčistší je reload stránky po uložení, ať se props načtou znovu
            location.reload();
        })
        .finally(() => (uploading.value = false));
};
</script>

<template>
    <div
        class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50"
    >
        <div class="relative w-full max-w-xl rounded-lg bg-white p-8 shadow-lg">
            <button
                class="absolute right-2 top-2 text-2xl text-gray-400"
                @click="$emit('close')"
            >
                &times;
            </button>
            <h3 class="mb-4 text-xl font-bold">
                Přiřadit službu do skupiny: {{ group.name }}
            </h3>

            <div class="mb-6 flex gap-4">
                <primary-button
                    @click="tab = 'vyber'"
                    :class="
                        tab == 'vyber'
                            ? 'font-bold text-blue-700 underline'
                            : 'rounded bg-blue-400 px-6 py-2 text-white hover:bg-blue-600'
                    "
                >
                    Vyber službu ze seznamu
                </primary-button>
                <primary-button
                    @click="tab = 'csv'"
                    :class="
                        tab == 'csv'
                            ? 'font-bold text-blue-700 underline'
                            : 'bg-green-900 text-gray-500'
                    "
                >
                    Import služeb z CSV
                </primary-button>
            </div>

            <div v-if="tab == 'vyber'">
                <form @submit.prevent="submit">
                    <select
                        v-model="tariffForm.tariff_id"
                        class="mb-4 w-full rounded border p-2"
                    >
                        <option value="" disabled>Vyberte službu</option>
                        <option
                            v-for="tariff in tariffs"
                            :key="tariff.id"
                            :value="tariff.id"
                        >
                            {{ tariff.name }}
                        </option>
                    </select>
                    <select
                        v-model="tariffForm.action"
                        class="mb-4 w-full rounded border p-2"
                    >
                        <option
                            v-for="a in tariffActions"
                            :key="a.value"
                            :value="a.value"
                        >
                            {{ a.label }}
                        </option>
                    </select>
                    <button
                        type="submit"
                        class="rounded bg-blue-500 px-6 py-2 text-white hover:bg-blue-600"
                    >
                        Přiřadit službu
                    </button>
                    <div
                        v-if="
                            tariffForm.errors.tariff_id ||
                            tariffForm.errors.action
                        "
                        class="mt-2 text-sm text-red-600"
                    >
                        <div v-if="tariffForm.errors.tariff_id">
                            {{ tariffForm.errors.tariff_id }}
                        </div>
                        <div v-if="tariffForm.errors.action">
                            {{ tariffForm.errors.action }}
                        </div>
                    </div>
                </form>
            </div>

            <div v-if="tab == 'csv'">
                <div class="mb-4">
                    <label class="mb-1 block font-semibold"
                        >Nahrát CSV soubor</label
                    >
                    <input
                        type="file"
                        accept=".csv"
                        @change="handleCSVFile"
                        class="rounded border p-2"
                    />
                </div>
                <div v-if="columns.length">
                    <label class="mb-1 block font-semibold"
                        >Vyberte sloupec se službami</label
                    >
                    <select
                        v-model="csvTariffColumn"
                        class="mb-2 w-full rounded border p-2"
                    >
                        <option value="" disabled>Vyberte sloupec</option>
                        <option v-for="col in columns" :key="col" :value="col">
                            {{ col }}
                        </option>
                    </select>
                    <button
                        v-if="csvTariffColumn"
                        @click="getUniqueTariffsFromCSV"
                        class="mb-4 rounded bg-blue-500 px-4 py-1 text-white"
                    >
                        Načíst unikátní hodnoty
                    </button>
                </div>
                <div v-if="uniqueTariffs.length">
                    <h4 class="mb-2 font-bold">
                        Unikátní služby nalezené v CSV:
                    </h4>
                    <ul
                        class="mb-4 max-h-40 overflow-auto rounded border bg-gray-50 p-2"
                    >
                        <li v-for="t in uniqueTariffs" :key="t">{{ t }}</li>
                    </ul>
                    <button
                        @click="saveUniqueTariffs"
                        class="mb-4 rounded bg-green-500 px-6 py-2 text-white hover:bg-green-600"
                        :disabled="uploading"
                    >
                        Uložit do databáze
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
<x></x>
