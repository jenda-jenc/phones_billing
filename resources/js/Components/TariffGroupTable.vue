<script setup>
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    groups: Array,
});
const emit = defineEmits(['edit', 'delete', 'attach-tariff']);

const removing = ref(false);

const removeTariff = async (groupId, tariffId) => {
    if (!groupId) return;
    if (!confirm('Opravdu odebrat tento tarif od skupiny?')) return;
    removing.value = true;
    await router.post(
        route('groups.detach-tariff'),
        {
            group_id: groupId,
            tariff_id: tariffId,
        },
        {
            preserveScroll: true,
            onFinish: () => (removing.value = false),
        },
    );
};
</script>

<template>
    <div class="overflow-hidden rounded-xl bg-gray-50 shadow-md">
        <table
            class="w-full table-auto rounded-md border border-gray-200 bg-white"
        >
            <thead>
                <tr class="bg-gray-600 text-blue-50">
                    <th class="p-2 text-left">Název</th>
                    <!--                <th class="text-left p-2">Kód / Hodnota</th>-->
                    <th class="p-2 text-left">Přiřazené služby</th>
                    <th class="w-48 p-2 text-left"></th>
                </tr>
            </thead>
            <tbody>
                <tr
                    v-for="group in groups"
                    :key="group.id"
                    class="border-t transition odd:bg-gray-50 even:bg-white hover:bg-blue-50"
                >
                    <td class="p-2 font-semibold">{{ group.name }}</td>
                    <!--                <td class="p-2">{{ group.value }}</td>-->
                    <td class="p-2">
                        <span v-if="group.tariffs && group.tariffs.length">
                            <span
                                v-for="tariff in group.tariffs"
                                :key="tariff.id"
                                class="mb-1 mr-1 inline-block rounded bg-gray-200 px-2 py-1"
                            >
                                {{ tariff.name }}
                                <span class="ml-2 text-xs text-gray-500"
                                    >({{ tariff.pivot.action }})</span
                                >
                                <button
                                    type="button"
                                    class="ml-1 text-red-500 hover:text-red-700"
                                    :disabled="removing"
                                    @click="removeTariff(group.id, tariff.id)"
                                >
                                    &times;
                                </button>
                            </span>
                        </span>
                        <span v-else class="text-gray-400">Žádné služby</span>
                    </td>
                    <td class="border-b px-4 py-2 text-center">
                        <div
                            class="flex flex-nowrap items-center justify-center gap-1"
                        >
                            <SecondaryButton
                                class="flex rounded bg-blue-500 text-xs text-gray-900 shadow transition-all duration-150 hover:bg-blue-600 hover:text-blue-500 hover:shadow-md"
                                @click="$emit('attach-tariff', group)"
                                >+tarif</SecondaryButton
                            >
                            <PrimaryButton
                                class="rounded bg-blue-600 px-2 py-1 text-xs hover:text-blue-500"
                                @click="$emit('edit', group)"
                                >Upravit</PrimaryButton
                            >
                            <DangerButton
                                class="rounded px-2 py-1 text-xs hover:bg-black hover:text-red-900"
                                @click="$emit('delete', group.id)"
                                >Odstranit</DangerButton
                            >
                        </div>
                    </td>
                </tr>
                <tr v-if="!groups.length">
                    <td colspan="4" class="p-4 text-center text-gray-500">
                        Žádné skupiny
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
