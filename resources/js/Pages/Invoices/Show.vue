<script setup>
import { computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    invoicePerson: {
        type: Object,
        required: true,
    },
});

const hasLines = computed(() => props.invoicePerson?.lines?.length > 0);

const formatCurrency = (value) => {
    if (value === null || value === undefined) {
        return '—';
    }

    return (
        Number(value).toLocaleString('cs-CZ', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        }) + ' Kč'
    );
};
</script>

<template>
    <Head title="Vyúčtování" />

    <AuthenticatedLayout>
        <template #header>
            <div
                class="flex flex-col gap-1 md:flex-row md:items-center md:justify-between"
            >
                <div>
                    <h2
                        class="text-xl font-semibold leading-tight text-gray-800"
                    >
                        Vyúčtování pro
                        {{
                            invoicePerson.person?.name ?? 'neznámého uživatele'
                        }}
                    </h2>
                    <p class="text-sm text-gray-500">
                        Telefon:
                        <span class="font-medium text-gray-700">{{
                            invoicePerson.phone
                        }}</span>
                    </p>
                </div>
                <div class="text-sm text-gray-500">
                    <p>
                        Faktura:
                        <span class="font-medium text-gray-700">
                            <template v-if="invoicePerson.invoice">
                                #{{ invoicePerson.invoice.id }}
                                <span
                                    v-if="invoicePerson.invoice.source_filename"
                                >
                                    –
                                    {{ invoicePerson.invoice.source_filename }}
                                </span>
                            </template>
                            <template v-else> Bez vazby </template>
                        </span>
                    </p>
                    <p v-if="invoicePerson.invoice?.created_at">
                        Vytvořeno: {{ invoicePerson.invoice.created_at }}
                    </p>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-5xl space-y-8 sm:px-6 lg:px-8">
                <div class="overflow-hidden rounded-lg bg-white shadow">
                    <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
                        <h3 class="text-lg font-medium text-gray-900">
                            Souhrn
                        </h3>
                    </div>
                    <div class="grid gap-4 px-6 py-6 sm:grid-cols-2">
                        <div>
                            <p class="text-sm text-gray-500">Limit</p>
                            <p class="text-lg font-semibold text-gray-900">
                                {{ formatCurrency(invoicePerson.limit) }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Celkem bez DPH</p>
                            <p class="text-lg font-semibold text-gray-900">
                                {{
                                    formatCurrency(
                                        invoicePerson.total_without_vat,
                                    )
                                }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Celkem s DPH</p>
                            <p class="text-lg font-semibold text-gray-900">
                                {{
                                    formatCurrency(invoicePerson.total_with_vat)
                                }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">K úhradě</p>
                            <p class="text-lg font-semibold text-gray-900">
                                {{ formatCurrency(invoicePerson.payable) }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="overflow-hidden rounded-lg bg-white shadow">
                    <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-medium text-gray-900">
                                Položky
                            </h3>
                            <Link
                                :href="
                                    route('invoices.email', {
                                        invoicePerson: invoicePerson.id,
                                    })
                                "
                                method="post"
                                as="button"
                                type="button"
                                class="rounded bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                            >
                                Odeslat e-mail
                            </Link>
                        </div>
                    </div>
                    <div v-if="hasLines" class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500"
                                    >
                                        Služba
                                    </th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500"
                                    >
                                        Tarif
                                    </th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500"
                                    >
                                        Skupina
                                    </th>
                                    <th
                                        class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500"
                                    >
                                        Cena bez DPH
                                    </th>
                                    <th
                                        class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500"
                                    >
                                        Cena s DPH
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                <tr
                                    v-for="line in invoicePerson.lines"
                                    :key="line.id"
                                    class="hover:bg-gray-50"
                                >
                                    <td
                                        class="whitespace-nowrap px-4 py-3 text-sm text-gray-700"
                                    >
                                        {{ line.service_name }}
                                    </td>
                                    <td
                                        class="whitespace-nowrap px-4 py-3 text-sm text-gray-700"
                                    >
                                        {{ line.tariff ?? '—' }}
                                    </td>
                                    <td
                                        class="whitespace-nowrap px-4 py-3 text-sm text-gray-700"
                                    >
                                        {{ line.group_name ?? '—' }}
                                    </td>
                                    <td
                                        class="whitespace-nowrap px-4 py-3 text-right text-sm text-gray-700"
                                    >
                                        {{
                                            formatCurrency(
                                                line.price_without_vat,
                                            )
                                        }}
                                    </td>
                                    <td
                                        class="whitespace-nowrap px-4 py-3 text-right text-sm text-gray-700"
                                    >
                                        {{
                                            formatCurrency(line.price_with_vat)
                                        }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-else class="px-6 py-6 text-sm text-gray-500">
                        Pro tuto osobu nejsou evidovány žádné položky.
                    </div>
                </div>

                <div
                    v-if="invoicePerson.applied_rules?.length"
                    class="overflow-hidden rounded-lg bg-white shadow"
                >
                    <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
                        <h3 class="text-lg font-medium text-gray-900">
                            Aplikovaná pravidla
                        </h3>
                    </div>
                    <ul class="divide-y divide-gray-200">
                        <li
                            v-for="(rule, index) in invoicePerson.applied_rules"
                            :key="index"
                            class="px-6 py-4 text-sm text-gray-700"
                        >
                            <p class="font-medium text-gray-900">
                                {{ rule.popis ?? 'Pravidlo' }}
                            </p>
                            <p v-if="rule.sluzba" class="text-gray-600">
                                Služba: {{ rule.sluzba }}
                            </p>
                            <p v-if="rule.cena_s_dph" class="text-gray-600">
                                Cena s DPH:
                                {{ formatCurrency(rule.cena_s_dph) }}
                            </p>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
