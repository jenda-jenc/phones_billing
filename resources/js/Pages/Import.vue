<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { computed, ref, watch, inject, nextTick } from 'vue';
import { getCurrentInstance } from 'vue';
import axios from 'axios';
import AssignServiceModal from '@/Components/AssignServiceModal.vue';

const { $inertia } = getCurrentInstance().appContext.config.globalProperties;
const loadingCustom = inject('loadingCustom', ref(false));

const columnsServices = ref([]);
const servicesFileObj = ref(null);
const billingPeriod = ref('');
const provider = ref('');

const mappingFields = ref({
    phone_number: { label: 'Telefonní číslo', value: '' },
    tarif: { label: 'Tarif', value: '', index: '' },
    service: { label: 'Služba', value: '', index: '' },
    price: { label: 'Částka', value: '', index: '' },
    vat: { label: 'Dph', value: '', index: '' },
});

const uniqueServices = ref([]);
const unknownServices = ref([]);
const unassignedServices = ref([]);
const servicesCsvText = ref('');
const isSavingTariffs = ref(false);

const showAssignModal = ref(false);
const assignServiceName = ref('');
const assignError = ref('');
const isAssigning = ref(false);

const allTariffNames = computed(() =>
    (usePage().props.tariffs || []).map((t) => t.name),
);
const allTariffs = computed(() => usePage().props.tariffs || []);
const allGroups = computed(() => usePage().props.groups || []);

const providerOptions = computed(() => {
    const providers = usePage().props.providers || {};

    return Object.entries(providers).map(([value, label]) => ({
        value,
        label,
    }));
});

const allGroupTariffNames = computed(() => {
    const groups = usePage().props.groups || [];
    const names = [];
    groups.forEach((group) => {
        (group.tariffs || []).forEach((tariff) => {
            names.push(tariff.name);
        });
    });
    return names;
});

// Pomocná funkce na výpočet unikátních/nových služeb podle dat
function refreshServiceWarnings() {
    const newVal = mappingFields.value.service.value;
    if (!newVal || !servicesFileObj.value || !servicesCsvText.value) {
        uniqueServices.value = [];
        unknownServices.value = [];
        unassignedServices.value = [];
        return;
    }
    const lines = servicesCsvText.value.split(/\r\n|\n/);
    const header = lines[0].split(';').map((c) => c.trim());
    const idx = header.indexOf(newVal);
    if (idx === -1) {
        uniqueServices.value = [];
        unknownServices.value = [];
        unassignedServices.value = [];
        console.log('Sloupec nenalezen:', newVal, header);
        return;
    }
    const set = new Set();
    for (let i = 1; i < lines.length; i++) {
        const cells = lines[i].split(';');
        if (cells.length > idx) {
            const val = (cells[idx] || '').trim();
            if (val) set.add(val);
        }
    }
    uniqueServices.value = Array.from(set);
    unknownServices.value = uniqueServices.value.filter(
        (s) => !allTariffNames.value.includes(s),
    );
    unassignedServices.value = uniqueServices.value.filter(
        (s) => !allGroupTariffNames.value.includes(s),
    );
}

// Sleduj výběr sloupce Služba a aktualizuj upozornění
watch(() => mappingFields.value.service.value, refreshServiceWarnings);

// --- AJAX pro získání aktuálních tarifů a skupin ---
const refreshTariffsAndGroups = async () => {
    try {
        const resp = await axios.get(route('import.data'));
        if (resp.data.tariffs) usePage().props.tariffs = resp.data.tariffs;
        if (resp.data.groups) usePage().props.groups = resp.data.groups;
        if (resp.data.providers) usePage().props.providers = resp.data.providers;
    } catch (e) {
        alert('Nepodařilo se aktualizovat seznam tarifů.');
    }
};

// --- Uložení nových tarifů do DB a aktualizace warningů bez reloadu stránky ---
const saveUnknownTariffs = async () => {
    if (!unknownServices.value.length) return;
    loadingCustom.value = true;
    isSavingTariffs.value = true;
    try {
        await axios.post(route('tariffs.bulk-store'), {
            names: unknownServices.value,
        });
        await refreshTariffsAndGroups();
        await nextTick();
        refreshServiceWarnings();
    } catch (err) {
        alert('Chyba při ukládání tarifů!');
    } finally {
        isSavingTariffs.value = false;
        loadingCustom.value = false;
    }
};

// --- MODAL logika ---
function openAssignModal(serviceName) {
    assignServiceName.value = serviceName;
    assignError.value = '';
    showAssignModal.value = true;
}
function closeAssignModal() {
    showAssignModal.value = false;
    assignServiceName.value = '';
    assignError.value = '';
}
const assignServiceToGroup = async ({ groupId, tariffId, action }) => {
    assignError.value = '';
    if (!tariffId || !groupId || !action) {
        assignError.value = 'Vyplňte všechny položky.';
        return;
    }
    isAssigning.value = true;
    loadingCustom.value = true;
    try {
        await axios.post(route('groups.attach-tariff'), {
            group_id: groupId,
            tariff_id: tariffId,
            action: action,
        });
        await refreshTariffsAndGroups();
        await nextTick();
        refreshServiceWarnings();
        closeAssignModal();
    } catch (e) {
        assignError.value = 'Nepodařilo se přiřadit službu ke skupině!';
    } finally {
        isAssigning.value = false;
        loadingCustom.value = false;
    }
};

const parseCSVHeader = (file) => {
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.onload = (e) => {
            let arrayBuffer = e.target.result;
            let decoder = new TextDecoder('utf-8');
            let text = decoder.decode(arrayBuffer);
            if (text.match(/[�]/)) {
                decoder = new TextDecoder('windows-1250');
                text = decoder.decode(arrayBuffer);
            }
            servicesCsvText.value = text;
            const lines = text.split(/\r\n|\n/);
            if (lines.length > 0) {
                const headerLine = lines[0];
                const columns = headerLine.split(';').map((col) => col.trim());
                resolve(columns);
            } else {
                reject(new Error('CSV soubor neobsahuje žádná data.'));
            }
        };
        reader.onerror = (error) => reject(error);
        reader.readAsArrayBuffer(file);
    });
};

const handleServicesFile = async (event) => {
    const file = event.target.files[0];
    if (file) {
        servicesFileObj.value = file;
        try {
            const headers = await parseCSVHeader(file);
            columnsServices.value = headers;
        } catch (error) {
            console.error('Chyba při načítání CSV souboru služeb:', error);
        }
    }
};

const processMapping = () => {
    const formData = new FormData();
    formData.append('services', servicesFileObj.value);
    formData.append('mapping', JSON.stringify(mappingFields.value));
    formData.append('billing_period', billingPeriod.value);
    formData.append('provider', provider.value);
    $inertia.post(route('import.process'), formData, {
        forceFormData: true,
    });
};

const canShowProcessButton = computed(() => {
    return (
        servicesFileObj.value !== null &&
        billingPeriod.value !== '' &&
        provider.value !== '' &&
        Object.values(mappingFields.value).every((field) => field.value !== '')
    );
});
</script>

<template>
    <Head title="Import CSV souborů" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-2xl font-bold leading-tight text-gray-800">
                Import CSV souborů
            </h2>
        </template>
        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div
                    class="overflow-hidden bg-white p-8 shadow-lg sm:rounded-lg"
                >
                    <!-- Sekce pro nahrání CSV souborů -->
                    <div class="grid grid-cols-1 justify-items-center gap-6">
                        <div class="text-center">
                            <h3
                                class="mb-2 text-lg font-semibold text-gray-700"
                            >
                                Nahrát CSV soubor služeb
                            </h3>
                            <div
                                class="flex items-center justify-center space-x-4"
                            >
                                <label class="cursor-pointer">
                                    <div
                                        class="rounded bg-blue-500 px-6 py-2 text-white shadow transition-all duration-150 hover:bg-blue-600 hover:shadow-lg"
                                    >
                                        Vyberte soubor
                                    </div>
                                    <input
                                        type="file"
                                        accept=".csv"
                                        class="hidden"
                                        @change="handleServicesFile"
                                    />
                                </label>
                            </div>
                        </div>
                        <div v-if="servicesFileObj" class="text-center text-sm">
                            <p>
                                Soubor
                                <i class="text-green-500">{{
                                    servicesFileObj.name
                                }}</i>
                                byl úspěšně nahrán!
                            </p>
                            <p>
                                V tabulce níže prosím přiřaďte sloupce z
                                nahraného CSV na hodnoty ve sloupci popis.
                            </p>
                        </div>
                    </div>
                    <div
                        class="mt-6 flex flex-col items-center justify-center gap-4 md:flex-row"
                    >
                        <label class="flex flex-col items-start gap-2 text-sm text-gray-700">
                            <span>Fakturační období</span>
                            <input
                                type="month"
                                v-model="billingPeriod"
                                class="rounded border px-3 py-2 text-base focus:outline-none focus:ring-2 focus:ring-blue-500"
                                required
                            />
                        </label>

                        <label class="flex flex-col items-start gap-2 text-sm text-gray-700">
                            <span>Poskytovatel</span>
                            <select
                                v-model="provider"
                                class="rounded border px-3 py-2 text-base focus:outline-none focus:ring-2 focus:ring-blue-500"
                                required
                            >
                                <option disabled value="">
                                    Vyberte poskytovatele
                                </option>
                                <option
                                    v-for="option in providerOptions"
                                    :key="option.value"
                                    :value="option.value"
                                >
                                    {{ option.label }}
                                </option>
                            </select>
                        </label>
                    </div>
                    <!-- Mapování sloupců -->
                    <div v-if="columnsServices.length" class="mt-10">
                        <h3
                            class="mb-4 text-center text-lg font-semibold text-gray-700 accent-green-500"
                        >
                            Mapování sloupců
                        </h3>
                        <table class="w-full table-auto border-collapse">
                            <thead>
                                <tr>
                                    <th
                                        class="border border-gray-300 bg-gray-50 px-4 py-2 text-left font-semibold text-gray-700"
                                    >
                                        Popis
                                    </th>
                                    <th
                                        class="border border-gray-300 bg-gray-50 px-4 py-2 text-left font-semibold text-gray-700"
                                    >
                                        Sloupec ze Služeb
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="(field, index) in mappingFields"
                                    :key="index"
                                    class="odd:bg-white even:bg-gray-50 hover:bg-gray-50"
                                >
                                    <td
                                        class="border border-gray-300 px-4 py-2 text-gray-600"
                                    >
                                        {{ field.label }}
                                    </td>
                                    <td
                                        class="border border-gray-300 px-4 py-2"
                                    >
                                        <div>
                                            <select
                                                v-model="field.value"
                                                @change="
                                                    field.index =
                                                        $event.target
                                                            .selectedIndex - 1
                                                "
                                                class="w-full rounded border p-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                            >
                                                <option disabled value="">
                                                    Vyberte sloupec z csv
                                                </option>
                                                <option
                                                    v-for="(
                                                        column, idx
                                                    ) in columnsServices"
                                                    :key="idx"
                                                    :value="column"
                                                >
                                                    {{ column }}
                                                </option>
                                            </select>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Služby, které nejsou v DB -->
                    <div v-if="unknownServices.length" class="mt-6">
                        <div
                            class="mb-2 rounded border border-yellow-400 bg-yellow-100 px-4 py-3 text-yellow-900"
                        >
                            <b>Pozor:</b> Tyto služby nejsou v databázi tarifů!
                        </div>
                        <ul class="flex flex-wrap gap-2">
                            <li
                                v-for="srv in unknownServices"
                                :key="srv"
                                class="rounded bg-yellow-200 px-2 py-1"
                            >
                                {{ srv }}
                            </li>
                        </ul>
                        <button
                            v-if="unknownServices.length"
                            @click="saveUnknownTariffs"
                            class="mt-4 rounded bg-green-600 px-6 py-2 text-white shadow hover:bg-green-700"
                            :disabled="isSavingTariffs"
                        >
                            {{
                                isSavingTariffs
                                    ? 'Ukládám...'
                                    : 'Uložit tyto služby do databáze'
                            }}
                        </button>
                    </div>

                    <!-- Služby, které nejsou přiřazeny žádné skupině -->
                    <div v-if="unassignedServices.length" class="mt-6">
                        <div
                            class="mb-2 rounded border border-blue-400 bg-blue-100 px-4 py-3 text-blue-900"
                        >
                            <b>Info:</b> Tyto služby nejsou přiřazeny k žádné
                            skupině tarifů:
                        </div>
                        <ul class="flex flex-wrap gap-2">
                            <li
                                v-for="srv in unassignedServices"
                                :key="srv"
                                class="flex items-center gap-2 rounded bg-blue-200 px-2 py-1"
                            >
                                <span>{{ srv }}</span>
                                <button
                                    class="rounded bg-blue-500 px-2 py-0.5 text-xs text-white hover:bg-blue-600"
                                    @click="openAssignModal(srv)"
                                >
                                    Přiřadit ke skupině
                                </button>
                            </li>
                        </ul>
                    </div>

                    <!-- Tlačítko pro spuštění zpracování -->
                    <div
                        v-if="canShowProcessButton"
                        class="mt-10 flex justify-center"
                    >
                        <button
                            @click="processMapping"
                            class="rounded bg-blue-500 px-8 py-2 text-white shadow transition-all duration-150 hover:bg-blue-600 hover:shadow-lg"
                        >
                            Zpracovat
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <!-- MODAL -->
        <AssignServiceModal
            :show="showAssignModal"
            :serviceName="assignServiceName"
            :groups="allGroups"
            :tariffs="allTariffs"
            :loading="isAssigning"
            :error="assignError"
            @close="closeAssignModal"
            @assign="assignServiceToGroup"
        />
    </AuthenticatedLayout>
</template>
