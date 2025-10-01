<script setup>
import { computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    invoices: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

const hasInvoices = computed(() => props.invoices?.data?.length > 0);

const formatCurrency = (value) => {
    if (value === null || value === undefined) {
        return '—';
    }

    return Number(value).toLocaleString('cs-CZ', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
};
</script>

<template>
    <Head title="Přehled faktur" />

    <AuthenticatedLayout>
        <template #header>
            <div
                class="flex flex-col gap-1 md:flex-row md:items-center md:justify-between"
            >
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Přehled faktur
                </h2>
                <p class="text-sm text-gray-500">
                    Role: {{ filters.role ?? 'nezjištěna' }}
                </p>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div v-if="hasInvoices" class="space-y-4">
                            <div class="overflow-x-auto">
                                <table
                                    class="min-w-full divide-y divide-gray-200"
                                >
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th
                                                scope="col"
                                                class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                            >
                                                ID
                                            </th>
                                            <th
                                                scope="col"
                                                class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                            >
                                                Soubor
                                            </th>
                                            <th
                                                scope="col"
                                                class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                            >
                                                Období
                                            </th>
                                            <th
                                                scope="col"
                                                class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                            >
                                                Vytvořeno
                                            </th>
                                            <th
                                                scope="col"
                                                class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500"
                                            >
                                                Osoby / Položky
                                            </th>
                                            <th
                                                scope="col"
                                                class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500"
                                            >
                                                Celkem bez DPH
                                            </th>
                                            <th
                                                scope="col"
                                                class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500"
                                            >
                                                Celkem s DPH
                                            </th>
                                            <th
                                                scope="col"
                                                class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500"
                                            >
                                                Limit / K úhradě
                                            </th>
                                            <th
                                                scope="col"
                                                class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500"
                                            >
                                                Stažení
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody
                                        class="divide-y divide-gray-200 bg-white"
                                    >
                                        <tr
                                            v-for="invoice in invoices.data"
                                            :key="invoice.id"
                                            class="hover:bg-gray-50"
                                        >
                                            <td
                                                class="whitespace-nowrap px-4 py-3 text-sm text-gray-700"
                                            >
                                                {{ invoice.id }}
                                            </td>
                                            <td
                                                class="whitespace-nowrap px-4 py-3 text-sm text-gray-700"
                                            >
                                                {{
                                                    invoice.source_filename ??
                                                    '—'
                                                }}
                                            </td>
                                            <td
                                                class="whitespace-nowrap px-4 py-3 text-sm text-gray-700"
                                            >
                                                {{
                                                    invoice.billing_period_label ??
                                                    invoice.billing_period ??
                                                    '—'
                                                }}
                                            </td>
                                            <td
                                                class="whitespace-nowrap px-4 py-3 text-sm text-gray-700"
                                            >
                                                {{ invoice.created_at ?? '—' }}
                                            </td>
                                            <td
                                                class="whitespace-nowrap px-4 py-3 text-right text-sm text-gray-700"
                                            >
                                                {{ invoice.people_count }} /
                                                {{ invoice.line_count }}
                                            </td>
                                            <td
                                                class="whitespace-nowrap px-4 py-3 text-right text-sm text-gray-700"
                                            >
                                                {{
                                                    formatCurrency(
                                                        invoice.totals
                                                            .without_vat,
                                                    )
                                                }}
                                            </td>
                                            <td
                                                class="whitespace-nowrap px-4 py-3 text-right text-sm text-gray-700"
                                            >
                                                {{
                                                    formatCurrency(
                                                        invoice.totals.with_vat,
                                                    )
                                                }}
                                            </td>
                                            <td
                                                class="whitespace-nowrap px-4 py-3 text-right text-sm text-gray-700"
                                            >
                                                {{
                                                    formatCurrency(
                                                        invoice.totals.limit,
                                                    )
                                                }}
                                                /
                                                {{
                                                    formatCurrency(
                                                        invoice.totals.payable,
                                                    )
                                                }}
                                            </td>
                                            <td
                                                class="whitespace-nowrap px-4 py-3 text-right text-sm"
                                            >
                                                <div
                                                    class="flex justify-end gap-2"
                                                >
                                                    <Link
                                                        :href="route('import.show', invoice.id)"
                                                        class="inline-flex items-center rounded border border-indigo-600 px-2 py-1 text-xs font-medium text-indigo-600 transition hover:bg-indigo-50"
                                                    >
                                                        Import
                                                    </Link>
                                                    <Link
                                                        v-if="
                                                            invoice.detail_url
                                                        "
                                                        :href="
                                                            invoice.detail_url
                                                        "
                                                        class="inline-flex items-center rounded border border-indigo-600 px-2 py-1 text-xs font-medium text-indigo-600 transition hover:bg-indigo-50"
                                                    >
                                                        Detail
                                                    </Link>
                                                    <a
                                                        :href="
                                                            invoice.downloads
                                                                .csv
                                                        "
                                                        class="inline-flex items-center rounded border border-gray-300 px-2 py-1 text-xs font-medium text-gray-700 transition hover:bg-gray-100"
                                                    >
                                                        CSV
                                                    </a>
                                                    <a
                                                        :href="
                                                            invoice.downloads
                                                                .pdf
                                                        "
                                                        class="inline-flex items-center rounded border border-gray-300 px-2 py-1 text-xs font-medium text-gray-700 transition hover:bg-gray-100"
                                                    >
                                                        PDF
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div
                                class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between"
                            >
                                <p class="text-sm text-gray-600">
                                    Zobrazeno {{ invoices.data.length }} z
                                    {{ invoices.total }} faktur.
                                </p>
                                <nav class="flex flex-wrap items-center gap-1">
                                    <Link
                                        v-for="link in invoices.links"
                                        :key="`${link.label}-${link.url}`"
                                        :href="link.url ?? '#'"
                                        class="rounded px-3 py-1 text-sm font-medium transition"
                                        :class="[
                                            link.active
                                                ? 'bg-indigo-600 text-white shadow'
                                                : 'bg-gray-100 text-gray-700 hover:bg-gray-200',
                                            !link.url
                                                ? 'cursor-not-allowed opacity-50'
                                                : '',
                                        ]"
                                        v-html="link.label"
                                        :aria-disabled="!link.url"
                                        @click="
                                            link.url
                                                ? null
                                                : $event.preventDefault()
                                        "
                                    />
                                </nav>
                            </div>
                        </div>

                        <div v-else class="text-center text-gray-500">
                            Zatím nejsou dostupné žádné faktury pro vaši roli.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
