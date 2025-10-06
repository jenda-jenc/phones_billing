<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { reactive, ref, computed, watch } from 'vue';
import axios from 'axios'; // Přidej pokud budeš posílat request na backend

const page = usePage();
const passedProps = defineProps({
    importData: {
        type: Object,
        default: null,
    },
    invoiceId: {
        type: [Number, String],
        default: null,
    },
    debtorsEmailRoute: {
        type: String,
        default: null,
    },
    defaultDebtorsEmail: {
        type: String,
        default: null,
    },
});

const rawImportData = computed(
    () => passedProps.importData ?? page.props.importData ?? null,
);
const safeImportData = computed(() => rawImportData.value ?? {});
const invoiceId = computed(
    () => passedProps.invoiceId ?? page.props.invoiceId ?? null,
);
const debtorsEmailRoute = computed(
    () => passedProps.debtorsEmailRoute ?? page.props.debtorsEmailRoute ?? null,
);
const defaultDebtorsEmail = computed(
    () =>
        passedProps.defaultDebtorsEmail ??
        page.props.defaultDebtorsEmail ??
        page.props.auth?.user?.email ??
        '',
);
const flash = computed(() => page.props.flash || {});
const isMissingImportData = computed(() => rawImportData.value === null);
const hasImportRows = computed(
    () => Object.keys(safeImportData.value).length > 0,
);

const expandedRows = reactive({});

const debtorsEmailState = reactive({
    email: '',
    isSending: false,
    message: '',
    errors: [],
});

watch(
    defaultDebtorsEmail,
    (value) => {
        if (!debtorsEmailState.email) {
            debtorsEmailState.email = value ?? '';
        }
    },
    { immediate: true },
);

function toggleRow(section, name, phone) {
    const key = `${section}-${name}-${phone}`;
    expandedRows[key] = !expandedRows[key];
}

// Filtrování dle osoby/čísla
const search = ref('');
const searchBy = ref('name');

const filteredImportData = computed(() => {
    const baseData = safeImportData.value;
    if (!search.value.trim()) return baseData;
    const needle = search.value.trim().toLowerCase();
    const result = {};
    Object.entries(baseData).forEach(([name, phones]) => {
        let matchedPhones = {};
        Object.entries(phones).forEach(([phone, data]) => {
            if (
                (searchBy.value === 'name' &&
                    name.toLowerCase().includes(needle)) ||
                (searchBy.value === 'phone' &&
                    phone.toLowerCase().includes(needle))
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
    if (!hasImportRows.value) return;
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
    const csv = [
        'Jméno,Telefon,Celkem,Celkem s DPH,Zaplatí,Limit',
        ...dataToExport.map((row) =>
            [
                row.jmeno,
                row.telefon,
                row.celkem,
                row.celkem_s_dph,
                row.zaplati,
                row.limit,
            ]
                .map((val) => `"${val ?? ''}"`)
                .join(','),
        ),
    ].join('\r\n');
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
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
    const invoicePersonId = data.invoice_person_id;

    if (!invoicePersonId) {
        notificationMessage.value[key] =
            'Chybí vazba na vyúčtování – nelze odeslat e-mail.';
        isSendingNotification.value[key] = false;
        return;
    }

    // 🟢 vezmeme e-mail z inputu, nebo fallback na původní data.name_email
    const emailToSend = data.emailInput?.trim() || data.name_email;

    try {
        const response = await axios.post(
            `/invoices/${invoicePersonId}/email`,
            { email: emailToSend },
        );
        console.log(response);
        notificationMessage.value[key] =
            response?.data?.message ?? 'E-mail byl úspěšně odeslán.';
    } catch (e) {
        notificationMessage.value[key] =
            e?.response?.data?.message ?? 'Chyba při odesílání e-mailu!';
    } finally {
        isSendingNotification.value[key] = false;
    }
}


async function sendDebtorsSummaryEmail() {
    debtorsEmailState.isSending = true;
    debtorsEmailState.message = '';
    debtorsEmailState.errors = [];

    if (!debtorsEmailRoute.value) {
        debtorsEmailState.message =
            'Chybí adresa pro odeslání souhrnného e-mailu.';
        debtorsEmailState.isSending = false;
        return;
    }

    try {
        const response = await axios.post(debtorsEmailRoute.value, {
            email: debtorsEmailState.email,
        });

        debtorsEmailState.message =
            response?.data?.message ?? 'Souhrnný e-mail byl odeslán.';
        debtorsEmailState.email = response?.data?.email ?? debtorsEmailState.email;
    } catch (error) {
        const responseData = error?.response?.data ?? {};

        debtorsEmailState.message =
            responseData?.message ?? 'Chyba při odesílání souhrnného e-mailu.';

        const emailErrors = responseData?.errors?.email ?? [];
        debtorsEmailState.errors = Array.isArray(emailErrors)
            ? emailErrors
            : emailErrors
                  ? [emailErrors].flat().map((value) => String(value))
                  : [];
    } finally {
        debtorsEmailState.isSending = false;
    }
}
</script>

<template>
    <Head title="Výpis vyúčtování" />

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="text-2xl font-bold leading-tight text-gray-800 md:text-3xl"
            >
                Výpis vyúčtování
            </h2>
        </template>

        <div
            v-if="isMissingImportData"
            class="mx-auto mt-6 max-w-3xl rounded-md border border-red-300 bg-red-50 px-4 py-3 text-red-900 shadow"
        >
            <span class="font-semibold">Chyba:</span>
            Nepodařilo se načíst data importu. Zkuste stránku načíst znovu nebo
            opakujte import.
        </div>

        <!-- Flash notifikace -->
        <div v-if="flash.success" class="mx-auto mt-6 max-w-3xl">
            <div
                class="rounded-md border border-green-300 bg-green-50 px-4 py-3 text-green-900 shadow transition-all duration-300"
            >
                <span class="font-semibold">Úspěch: </span> {{ flash.success }}
            </div>
        </div>
        <div v-if="flash.error" class="mx-auto mt-6 max-w-3xl">
            <div
                class="bg-blubg-red-50 rounded-md border border-red-300 px-4 py-3 text-red-900 shadow transition-all duration-300"
            >
                <span class="font-semibold">Chyba: </span> {{ flash.error }}
            </div>
        </div>

        <!-- Sekce Dlužníci z importu -->
        <div
            class="mx-auto mt-6 max-w-7xl rounded-xl border border-gray-200 bg-white p-6 shadow-md"
        >
            <h1 class="mb-4 text-xl font-semibold text-red-500">
                Dlužníci z importu
            </h1>
            <p v-if="invoiceId" class="mb-4 text-sm text-gray-500">
                Vyúčtování #{{ invoiceId }}
            </p>
            <form
                class="mb-6 rounded-lg border border-gray-200 bg-gray-50 p-4 shadow-sm"
                @submit.prevent="sendDebtorsSummaryEmail"
            >
                <div class="flex flex-col gap-4 md:flex-row md:items-end">
                    <div class="flex-1">
                        <label
                            for="debtors-email"
                            class="mb-1 block text-sm font-semibold text-gray-700"
                        >
                            E-mail pro souhrn dlužníků
                        </label>
                        <input
                            id="debtors-email"
                            v-model="debtorsEmailState.email"
                            type="email"
                            :placeholder="
                                page.props.auth?.user?.email ?? 'Zadejte e-mail'
                            "
                            class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                        />
                        <ul
                            v-if="debtorsEmailState.errors.length"
                            class="mt-2 list-disc space-y-1 pl-4 text-sm text-red-600"
                        >
                            <li
                                v-for="(
                                    error, index
                                ) in debtorsEmailState.errors"
                                :key="`debtors-email-error-${index}`"
                            >
                                {{ error }}
                            </li>
                        </ul>
                    </div>
                    <div class="flex items-center gap-3 md:w-auto">
                        <button
                            type="submit"
                            class="whitespace-nowrap rounded bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow transition hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-60"
                            :disabled="
                                debtorsEmailState.isSending ||
                                !debtorsEmailRoute
                            "
                        >
                            <span v-if="debtorsEmailState.isSending"
                                >Odesílám…</span
                            >
                            <span v-else>Odeslat souhrnný e-mail</span>
                        </button>
                        <span
                            v-if="debtorsEmailState.message"
                            :class="[
                                'text-sm font-semibold',
                                debtorsEmailState.errors.length
                                    ? 'text-red-600'
                                    : 'text-green-600',
                            ]"
                        >
                            {{ debtorsEmailState.message }}
                        </span>
                    </div>
                </div>
                <p
                    v-if="!debtorsEmailRoute"
                    class="mt-2 text-sm text-orange-600"
                >
                    Není k dispozici adresa pro odeslání. Zkontrolujte
                    konfiguraci aplikace.
                </p>
            </form>
            <div
                v-if="!isMissingImportData && !hasImportRows"
                class="rounded-md border border-yellow-300 bg-yellow-50 px-4 py-3 text-sm text-yellow-900"
            >
                Aktuální import neobsahuje žádné položky k zobrazení.
            </div>
            <div
                v-if="!isMissingImportData && hasImportRows"
                class="overflow-x-auto rounded-xl border border-gray-200 bg-gray-50 shadow-inner"
            >
                <table
                    class="w-full table-auto rounded-md border border-gray-200 bg-white"
                >
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
                        <template
                            v-for="(phones, name) in filteredImportData"
                            :key="name"
                        >
                            <template
                                v-for="(data, phone) in phones"
                                :key="phone"
                            >
                                <tr
                                    v-if="parseFloat(data.zaplati) > 0"
                                    @click="toggleRow('import', name, phone)"
                                    class="cursor-pointer border-b transition hover:bg-gray-100"
                                    :class="{
                                        'bg-red-100':
                                            parseFloat(data.zaplati) > 0,
                                    }"
                                >
                                    <td class="px-4 py-2 font-medium">
                                        {{ name }}
                                    </td>
                                    <td class="px-4 py-2">{{ phone }}</td>
                                    <td class="px-4 py-2 text-right">
                                        {{ data.celkem }} Kč
                                    </td>
                                    <td class="px-4 py-2 text-right">
                                        {{ data.celkem_s_dph }} Kč
                                    </td>
                                    <td
                                        class="px-4 py-2 text-right font-bold text-red-500"
                                    >
                                        {{ data.zaplati }} Kč
                                    </td>
                                    <td class="px-4 py-2 text-right">
                                        {{ data.limit }} Kč
                                    </td>
                                </tr>
                                <!-- Detailní řádek -->
                                <tr
                                    v-if="
                                        expandedRows[`import-${name}-${phone}`]
                                    "
                                >
                                    <td
                                        colspan="7"
                                        class="rounded-b-xl border-b bg-white px-4 py-4 text-sm"
                                    >
                                        <h3
                                            class="mb-2 font-semibold text-gray-700"
                                        >
                                            Podrobnosti služeb:
                                        </h3>
                                        <table
                                            class="mb-3 w-full rounded-lg border border-gray-200 text-sm shadow-inner"
                                        >
                                            <thead>
                                                <tr>
                                                    <th
                                                        class="border-b pb-1 text-left"
                                                    >
                                                        Tarif
                                                    </th>
                                                    <th
                                                        class="border-b pb-1 text-left"
                                                    >
                                                        Služba
                                                    </th>
                                                    <th
                                                        class="border-b pb-1 text-right"
                                                    >
                                                        Cena bez DPH
                                                    </th>
                                                    <th
                                                        class="border-b pb-1 text-right"
                                                    >
                                                        Cena s DPH
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr
                                                    v-for="(
                                                        sluzba, idx
                                                    ) in data.sluzby"
                                                    :key="idx"
                                                >
                                                    <td class="border-t py-1">
                                                        {{ sluzba.tarif }}
                                                    </td>
                                                    <td class="border-t py-1">
                                                        {{ sluzba.sluzba }}
                                                    </td>
                                                    <td
                                                        class="border-t py-1 text-right"
                                                    >
                                                        {{ sluzba.cena }} Kč
                                                    </td>
                                                    <td
                                                        class="border-t py-1 text-right"
                                                    >
                                                        {{ sluzba.cena_s_dph }}
                                                        Kč
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                        <h4
                                            class="mb-2 mt-4 font-semibold text-gray-700"
                                        >
                                            Aplikovaná pravidla:
                                        </h4>
                                        <ul
                                            class="ml-4 list-disc text-gray-600"
                                        >
                                            <li
                                                v-for="(
                                                    pravidlo, idx
                                                ) in data.aplikovana_pravidla"
                                                :key="idx"
                                            >
                                                <ul>
                                                    <li>
                                                        <span>
                                                            <strong
                                                                v-if="
                                                                    pravidlo.skupina
                                                                "
                                                                >{{
                                                                    pravidlo.skupina
                                                                }}:</strong
                                                            >
                                                            <span>
                                                                <strong
                                                                    v-if="
                                                                        pravidlo.sluzba
                                                                    "
                                                                    >{{
                                                                        pravidlo.popis
                                                                    }}
                                                                    -
                                                                    {{
                                                                        pravidlo.sluzba
                                                                    }}</strong
                                                                >
                                                            </span>
                                                        </span>
                                                    </li>
                                                    <li>
                                                        <span>
                                                            <span
                                                                v-if="
                                                                    pravidlo.cena_bez_dph
                                                                "
                                                                ><strong
                                                                    >{{
                                                                        pravidlo.cena_bez_dph
                                                                    }}
                                                                    Kč</strong
                                                                >
                                                                bez DPH</span
                                                            >
                                                        </span>
                                                    </li>
                                                    <li>
                                                        <span>
                                                            <span
                                                                v-if="
                                                                    pravidlo.cena_bez_dph
                                                                "
                                                                ><strong
                                                                    >{{
                                                                        pravidlo.cena_s_dph
                                                                    }}
                                                                    Kč</strong
                                                                >
                                                                s DPH</span
                                                            >
                                                        </span>
                                                    </li>
                                                </ul>
                                            </li>
                                        </ul>
                                        <!-- Tlačítko Odeslat oznámení -->
                                        <div
                                            class="mt-6 flex items-center gap-3"
                                        >
                                            <input
                                                v-model="data.emailInput"
                                                type="email"
                                                :placeholder="data.name_email"
                                                class="rounded border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                            />
                                            <button
                                                class="rounded bg-blue-600 px-4 py-2 text-white shadow transition hover:bg-blue-700 disabled:opacity-60"
                                                :disabled="
                                                    isSendingNotification[
                                                        `${name}-${phone}`
                                                    ]
                                                "
                                                @click.stop="
                                                    sendNotification(
                                                        name,
                                                        phone,
                                                        data,
                                                    )
                                                "
                                            >
                                                <span
                                                    v-if="
                                                        isSendingNotification[
                                                            `${name}-${phone}`
                                                        ]
                                                    "
                                                    >Odesílám…</span
                                                >
                                                <span v-else
                                                    >Odeslat oznámení</span
                                                >
                                            </button>
                                            <span
                                                v-if="
                                                    notificationMessage[
                                                        `${name}-${phone}`
                                                    ]
                                                "
                                                class="font-semibold text-green-600"
                                            >
                                                {{
                                                    notificationMessage[
                                                        `${name}-${phone}`
                                                    ]
                                                }}
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
        <div
            class="mx-auto mt-10 max-w-7xl rounded-xl border border-gray-200 bg-white p-6 shadow-md"
        >
            <!-- Panel vyhledávání v horní části cardu -->
            <h1 class="mb-4 text-lg font-semibold text-gray-800">
                Výsledky Importu
            </h1>
            <div
                class="overflow-x-auto rounded-xl border border-gray-200 bg-gray-50 shadow-inner"
            >
                <div
                    class="mb-4 flex flex-col gap-4 p-7 md:flex-row md:items-center md:justify-between"
                >
                    <div class="flex items-center gap-4">
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
                            :placeholder="
                                searchBy === 'name'
                                    ? 'Hledejte podle jména'
                                    : 'Hledejte podle telefonního čísla'
                            "
                            class="w-full rounded border p-2"
                        />
                    </div>
                    <div>
                        <button
                            @click="saveReport"
                            class="rounded bg-blue-600 px-6 py-2 text-white shadow hover:bg-blue-700"
                        >
                            Uložit výpis (.csv)
                        </button>
                    </div>
                </div>
                <table
                    class="w-full table-auto rounded-md border border-gray-200 bg-white"
                >
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
                        <template
                            v-for="(phones, name) in filteredImportData"
                            :key="name"
                        >
                            <template
                                v-for="(data, phone) in phones"
                                :key="phone"
                            >
                                <tr
                                    class="transition odd:bg-gray-50 even:bg-white hover:bg-blue-50"
                                    :class="{
                                        'bg-green-100':
                                            parseFloat(data.zaplati) === 0,
                                    }"
                                    @click="toggleRow('result', name, phone)"
                                >
                                    <td class="border-b px-4 py-2 font-medium">
                                        {{ name }}
                                    </td>
                                    <td class="border-b px-4 py-2">
                                        {{ phone }}
                                    </td>
                                    <td class="border-b px-4 py-2 text-right">
                                        {{ data.celkem }} Kč
                                    </td>
                                    <td class="border-b px-4 py-2 text-right">
                                        {{ data.celkem_s_dph }} Kč
                                    </td>
                                    <td class="border-b px-7 py-2 text-right">
                                        <span
                                            :class="{
                                                'font-semibold text-red-600':
                                                    parseFloat(data.zaplati) >
                                                    0,
                                            }"
                                        >
                                            {{ data.zaplati }} Kč
                                        </span>
                                    </td>
                                    <td
                                        class="mx-7 border-b px-2 py-2 text-right"
                                    >
                                        {{ data.limit }} Kč
                                    </td>
                                </tr>
                                <!-- Detailní řádek -->
                                <tr
                                    v-if="
                                        expandedRows[`result-${name}-${phone}`]
                                    "
                                >
                                    <td
                                        colspan="6"
                                        class="rounded-b-xl border-b bg-white px-4 py-4 text-sm"
                                    >
                                        <h3
                                            class="mb-2 font-semibold text-gray-700"
                                        >
                                            Podrobnosti služeb:
                                        </h3>
                                        <table
                                            class="mb-3 w-full rounded-lg border border-gray-200 text-sm shadow-inner"
                                        >
                                            <thead>
                                                <tr>
                                                    <th
                                                        class="border-b pb-1 text-left"
                                                    >
                                                        Tarif
                                                    </th>
                                                    <th
                                                        class="border-b pb-1 text-left"
                                                    >
                                                        Služba
                                                    </th>
                                                    <th
                                                        class="border-b pb-1 text-right"
                                                    >
                                                        Cena bez DPH
                                                    </th>
                                                    <th
                                                        class="border-b pb-1 text-right"
                                                    >
                                                        Cena s DPH
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr
                                                    v-for="(
                                                        sluzba, idx
                                                    ) in data.sluzby"
                                                    :key="idx"
                                                >
                                                    <td class="border-t py-1">
                                                        {{ sluzba.tarif }}
                                                    </td>
                                                    <td class="border-t py-1">
                                                        {{ sluzba.sluzba }}
                                                    </td>
                                                    <td
                                                        class="border-t py-1 text-right"
                                                    >
                                                        {{ sluzba.cena }} Kč
                                                    </td>
                                                    <td
                                                        class="border-t py-1 text-right"
                                                    >
                                                        {{ sluzba.cena_s_dph }}
                                                        Kč
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                        <h4
                                            class="mb-2 mt-4 font-semibold text-gray-700"
                                        >
                                            Aplikovaná pravidla:
                                        </h4>
                                        <ul
                                            class="ml-4 list-disc text-gray-600"
                                        >
                                            <li
                                                v-for="(
                                                    pravidlo, idx
                                                ) in data.aplikovana_pravidla"
                                                :key="idx"
                                            >
                                                <ul>
                                                    <li>
                                                        <span>
                                                            <strong
                                                                v-if="
                                                                    pravidlo.skupina
                                                                "
                                                                >{{
                                                                    pravidlo.skupina
                                                                }}:</strong
                                                            >
                                                            <span>
                                                                <strong
                                                                    v-if="
                                                                        pravidlo.sluzba
                                                                    "
                                                                    >{{
                                                                        pravidlo.popis
                                                                    }}
                                                                    -
                                                                    {{
                                                                        pravidlo.sluzba
                                                                    }}</strong
                                                                >
                                                            </span>
                                                        </span>
                                                    </li>
                                                    <li>
                                                        <span>
                                                            <span
                                                                v-if="
                                                                    pravidlo.cena_bez_dph
                                                                "
                                                                ><strong
                                                                    >{{
                                                                        pravidlo.cena_bez_dph
                                                                    }}
                                                                    Kč</strong
                                                                >
                                                                bez DPH</span
                                                            >
                                                        </span>
                                                    </li>
                                                    <li>
                                                        <span>
                                                            <span
                                                                v-if="
                                                                    pravidlo.cena_bez_dph
                                                                "
                                                                ><strong
                                                                    >{{
                                                                        pravidlo.cena_s_dph
                                                                    }}
                                                                    Kč</strong
                                                                >
                                                                s DPH</span
                                                            >
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
