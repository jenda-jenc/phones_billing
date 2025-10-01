<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {Head, usePage} from '@inertiajs/vue3';
import {reactive, ref, computed} from 'vue';
import axios from 'axios'; // Přidej pokud budeš posílat request na backend

const {props} = usePage();
const importData = reactive(props.importData || {});
const flash = computed(() => props.flash || {});

const expandedRows = reactive({});

function toggleRow(section, name, phone) {
    const key = `${section}-${name}-${phone}`;
    expandedRows[key] = !expandedRows[key];
}

// Filtrování dle osoby/čísla
const search = ref('');
const searchBy = ref('name');

const filteredImportData = computed(() => {
    if (!search.value.trim()) return importData;
    const needle = search.value.trim().toLowerCase();
    const result = {};
    Object.entries(importData).forEach(([name, phones]) => {
        let matchedPhones = {};
        Object.entries(phones).forEach(([phone, data]) => {
            if (
                (searchBy.value === 'name' && name.toLowerCase().includes(needle)) ||
                (searchBy.value === 'phone' && phone.toLowerCase().includes(needle))
            ) {
                matchedPhones[phone] = data;
            }
        });
        if (Object.keys(matchedPhones).length) {
            result[name] = matchedPhones;
        }
    });
    return result;
});

// Export do CSV
function saveReport() {
    const dataToExport = [];
    Object.entries(filteredImportData.value).forEach(([name, phones]) => {
        Object.entries(phones).forEach(([phone, data]) => {
            dataToExport.push({
                jmeno: name,
                telefon: phone,
                celkem: data.celkem,
                celkem_s_dph: data.celkem_s_dph,
                zaplati: data.zaplati,
                limit: data.limit,
            });
        });
    });
    const csv =
        [
            'Jméno,Telefon,Celkem,Celkem s DPH,Zaplatí,Limit',
            ...dataToExport.map(row =>
                [row.jmeno, row.telefon, row.celkem, row.celkem_s_dph, row.zaplati, row.limit]
                    .map(val => `"${val ?? ''}"`).join(',')
            ),
        ].join('\r\n');
    const blob = new Blob([csv], {type: 'text/csv;charset=utf-8;'});
    const url = window.URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = `vyuctovani_export_${new Date().toISOString().slice(0, 10)}.csv`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    window.URL.revokeObjectURL(url);
}

// --- Nové pro odesílání emailu dlužníkovi ---
const isSendingNotification = ref({});
const notificationMessage = ref({});

async function sendNotification(name, phone, data) {
    const key = `${name}-${phone}`;
    isSendingNotification.value[key] = true;
    notificationMessage.value[key] = '';
    try {
        // TODO: ZDE POZDĚJI NAHRADÍŠ ENDPOINTEM (např. await axios.post('/api/send-notification', { ... }))
        // await axios.post('/api/send-notification', { name, phone, data });
        await new Promise(resolve => setTimeout(resolve, 1300)); // Dummy simulace
        notificationMessage.value[key] = 'E-mail byl úspěšně odeslán!(Dělám si srandu, nebyl.)';
    } catch (e) {
        notificationMessage.value[key] = 'Chyba při odesílání e-mailu!';
    } finally {
        isSendingNotification.value[key] = false;
    }
}
</script>

<template>
    <Head title="Výpis vyúčtování"/>

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-2xl md:text-3xl font-bold leading-tight text-gray-800">
                Výpis vyúčtování
            </h2>
        </template>

        <!-- Flash notifikace -->
        <div v-if="flash.success" class="max-w-3xl mx-auto mt-6">
            <div
                class="bg-green-50 border border-green-300 text-green-900 px-4 py-3 rounded-md shadow transition-all duration-300">
                <span class="font-semibold">Úspěch: </span> {{ flash.success }}
            </div>
        </div>
        <div v-if="flash.error" class="max-w-3xl mx-auto mt-6">
            <div
                class="bg-blubg-red-50 border border-red-300 text-red-900 px-4 py-3 rounded-md shadow transition-all duration-300">
                <span class="font-semibold">Chyba: </span> {{ flash.error }}
            </div>
        </div>

        <!-- Sekce Dlužníci z importu -->
        <div class="mx-auto max-w-7xl mt-6 bg-white rounded-xl shadow-md border border-gray-200 p-6">
            <h1 class="text-xl font-semibold text-red-500 mb-4">Dlužníci z importu</h1>
            <div class="overflow-x-auto rounded-xl shadow-inner bg-gray-50 border border-gray-200">
                <table class="table-auto w-full bg-white border border-gray-200 rounded-md">
                    <thead>
                    <tr class="bg-gray-600 text-blue-50">
                        <th class="px-4 py-2 text-left">Jméno</th>
                        <th class="px-4 py-2 text-left">Telefon</th>
                        <th class="px-4 py-2 text-right">Celkem</th>
                        <th class="px-4 py-2 text-right">Celkem s DPH</th>
                        <th class="px-4 py-2 text-right">Zaplati</th>
                        <th class="px-4 py-2 text-right">Limit</th>
                    </tr>
                    </thead>
                    <tbody>
                    <template v-for="(phones, name) in importData" :key="name">
                        <template v-for="(data, phone) in phones" :key="phone">
                            <tr v-if="parseFloat(data.zaplati) > 0"
                                @click="toggleRow('import', name, phone)"
                                class="cursor-pointer border-b hover:bg-gray-100 transition"
                                :class="{ 'bg-red-100': parseFloat(data.zaplati) > 0 }">
                                <td class="px-4 py-2 font-medium">{{ name }}</td>
                                <td class="px-4 py-2">{{ phone }}</td>
                                <td class="px-4 py-2 text-right">{{ data.celkem }} Kč</td>
                                <td class="px-4 py-2 text-right">{{ data.celkem_s_dph }} Kč</td>
                                <td class="px-4 py-2 text-right text-red-500 font-bold">{{ data.zaplati }} Kč</td>
                                <td class="px-4 py-2 text-right">{{ data.limit }} Kč</td>
                            </tr>
                            <!-- Detailní řádek -->
                            <tr v-if="expandedRows[`import-${name}-${phone}`]">
                                <td colspan="7" class="bg-white px-4 py-4 text-sm border-b rounded-b-xl">
                                    <h3 class="font-semibold text-gray-700 mb-2">Podrobnosti služeb:</h3>
                                    <table class="w-full text-sm mb-3 rounded-lg border border-gray-200 shadow-inner">
                                        <thead>
                                        <tr>
                                            <th class="border-b pb-1 text-left">Tarif</th>
                                            <th class="border-b pb-1 text-left">Služba</th>
                                            <th class="border-b pb-1 text-right">Cena bez DPH</th>
                                            <th class="border-b pb-1 text-right">Cena s DPH</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr v-for="(sluzba, idx) in data.sluzby" :key="idx">
                                            <td class="border-t py-1">{{ sluzba.tarif }}</td>
                                            <td class="border-t py-1">{{ sluzba.sluzba }}</td>
                                            <td class="border-t py-1 text-right">{{ sluzba.cena }} Kč</td>
                                            <td class="border-t py-1 text-right">{{ sluzba.cena_s_dph }} Kč</td>
                                        </tr>
                                        </tbody>
                                    </table>
                                    <h4 class="font-semibold text-gray-700 mb-2 mt-4">Aplikovaná pravidla:</h4>
                                    <ul class="ml-4 list-disc text-gray-600">
                                        <li v-for="(pravidlo, idx) in data.aplikovana_pravidla" :key="idx">
                                            <ul>
                                                <li>
                                                <span>
                                                <strong v-if="pravidlo.skupina">{{ pravidlo.skupina }}:</strong>
                                                    <span>
                                                        <strong v-if="pravidlo.sluzba">{{
                                                                pravidlo.popis
                                                            }} -  {{ pravidlo.sluzba }}</strong>
                                                    </span>
                                                </span>
                                                </li>
                                                <li>
                                                <span>
                                                    <span v-if="pravidlo.cena_bez_dph"><strong>{{
                                                            pravidlo.cena_bez_dph
                                                        }} Kč</strong> bez DPH</span>
                                                </span>
                                                </li>
                                                <li>
                                                <span>
                                                    <span v-if="pravidlo.cena_bez_dph"><strong>{{ pravidlo.cena_s_dph }} Kč</strong> s DPH</span>
                                                </span>
                                                </li>
                                            </ul>
                                        </li>
                                    </ul>
                                    <!-- Tlačítko Odeslat oznámení -->
                                    <div class="flex items-center gap-3 mt-6">
                                        <button
                                            class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition shadow disabled:opacity-60"
                                            :disabled="isSendingNotification[`${name}-${phone}`]"
                                            @click.stop="sendNotification(name, phone, data)"
                                        >
                                            <span v-if="isSendingNotification[`${name}-${phone}`]">Odesílám…</span>
                                            <span v-else>Odeslat oznámení</span>
                                        </button>
                                        <span v-if="notificationMessage[`${name}-${phone}`]"
                                              class="text-green-600 font-semibold">
                                            {{ notificationMessage[`${name}-${phone}`] }}
                                        </span>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </template>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Výsledky Importu - vizuálně sjednocené -->
        <div class="mx-auto max-w-7xl mt-10 bg-white rounded-xl shadow-md border border-gray-200 p-6">
            <!-- Panel vyhledávání v horní části cardu -->
            <h1 class="text-lg font-semibold text-gray-800 mb-4">Výsledky Importu</h1>
            <div class="overflow-x-auto rounded-xl shadow-inner bg-gray-50 border border-gray-200">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-4 p-7">
                    <div class="flex gap-4 items-center">
                        <label class="flex items-center gap-2">
                            <input
                                type="radio"
                                name="searchBy"
                                value="name"
                                v-model="searchBy"
                                class="form-radio text-blue-600"
                            />
                            <span>Jméno</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input
                                type="radio"
                                name="searchBy"
                                value="phone"
                                v-model="searchBy"
                                class="form-radio text-blue-600"
                            />
                            <span>Tel.</span>
                        </label>
                    </div>
                    <div class="flex-1">
                        <input
                            v-model="search"
                            type="text"
                            :placeholder="searchBy === 'name' ? 'Hledejte podle jména' : 'Hledejte podle telefonního čísla'"
                            class="w-full p-2 border rounded"
                        />
                    </div>
                    <div>
                        <button
                            @click="saveReport"
                            class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 shadow"
                        >
                            Uložit výpis (.csv)
                        </button>
                    </div>
                </div>
                <table class="table-auto w-full bg-white border border-gray-200 rounded-md">
                    <thead>
                    <tr class="bg-gray-600 text-blue-50">
                        <th class="px-4 py-4 text-left">Jméno</th>
                        <th class="px-4 py-4 text-left">Telefon</th>
                        <th class="px-4 py-4 text-right">Celkem</th>
                        <th class="px-4 py-4 text-right">Celkem s DPH</th>
                        <th class="px-4 py-4 text-right">Zaplati</th>
                        <th class="px-7 py-4 text-right">Limit</th>
                    </tr>
                    </thead>
                    <tbody>
                    <template v-for="(phones, name) in filteredImportData" :key="name">
                        <template v-for="(data, phone) in phones" :key="phone">
                            <tr class="odd:bg-gray-50 even:bg-white hover:bg-blue-50 transition"
                                :class="{ 'bg-green-100': parseFloat(data.zaplati) === 0 }"
                                @click="toggleRow('result', name, phone)">
                                <td class="px-4 py-2 border-b font-medium">{{ name }}</td>
                                <td class="px-4 py-2 border-b">{{ phone }}</td>
                                <td class="px-4 py-2 border-b text-right">{{ data.celkem }} Kč</td>
                                <td class="px-4 py-2 border-b text-right">{{ data.celkem_s_dph }} Kč</td>
                                <td class="px-7 py-2 border-b text-right">
                                        <span :class="{ 'text-red-600 font-semibold': parseFloat(data.zaplati) > 0 }">
                                            {{ data.zaplati }} Kč
                                        </span>
                                </td>
                                <td class="mx-7 px-2 py-2 border-b text-right">{{ data.limit }} Kč</td>
                            </tr>
                            <!-- Detailní řádek -->
                            <tr v-if="expandedRows[`result-${name}-${phone}`]">
                                <td colspan="6" class="bg-white px-4 py-4 text-sm border-b rounded-b-xl">
                                    <h3 class="font-semibold text-gray-700 mb-2">Podrobnosti služeb:</h3>
                                    <table class="w-full text-sm mb-3 rounded-lg border border-gray-200 shadow-inner">
                                        <thead>
                                        <tr>
                                            <th class="border-b pb-1 text-left">Tarif</th>
                                            <th class="border-b pb-1 text-left">Služba</th>
                                            <th class="border-b pb-1 text-right">Cena bez DPH</th>
                                            <th class="border-b pb-1 text-right">Cena s DPH</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr v-for="(sluzba, idx) in data.sluzby" :key="idx">
                                            <td class="border-t py-1">{{ sluzba.tarif }}</td>
                                            <td class="border-t py-1">{{ sluzba.sluzba }}</td>
                                            <td class="border-t py-1 text-right">{{ sluzba.cena }} Kč</td>
                                            <td class="border-t py-1 text-right">{{ sluzba.cena_s_dph }} Kč</td>
                                        </tr>
                                        </tbody>
                                    </table>
                                    <h4 class="font-semibold text-gray-700 mb-2 mt-4">Aplikovaná pravidla:</h4>
                                    <ul class="ml-4 list-disc text-gray-600">
                                        <li v-for="(pravidlo, idx) in data.aplikovana_pravidla" :key="idx">
                                            <ul>
                                                <li>
                                                <span>
                                                <strong v-if="pravidlo.skupina">{{ pravidlo.skupina }}:</strong>
                                                    <span>
                                                        <strong v-if="pravidlo.sluzba">{{
                                                                pravidlo.popis
                                                            }} -  {{ pravidlo.sluzba }}</strong>
                                                    </span>
                                                </span>
                                                </li>
                                                <li>
                                                <span>
                                                    <span v-if="pravidlo.cena_bez_dph"><strong>{{
                                                            pravidlo.cena_bez_dph
                                                        }} Kč</strong> bez DPH</span>
                                                </span>
                                                </li>
                                                <li>
                                                <span>
                                                    <span v-if="pravidlo.cena_bez_dph"><strong>{{ pravidlo.cena_s_dph }} Kč</strong> s DPH</span>
                                                </span>
                                                </li>
                                            </ul>
                                        </li>
                                    </ul>
                                </td>
                            </tr>
                        </template>
                    </template>
                    </tbody>
                </table>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
