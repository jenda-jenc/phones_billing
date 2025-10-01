<script setup>
import PrimaryButton from "@/Components/PrimaryButton.vue";
import DangerButton from "@/Components/DangerButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import {router} from "@inertiajs/vue3";
import {ref} from 'vue';

const props = defineProps({
    groups: Array
});
const emit = defineEmits(['edit', 'delete', 'attach-tariff']);

const removing = ref(false);

const removeTariff = async (groupId, tariffId) => {
    if (!groupId) return;
    if (!confirm('Opravdu odebrat tento tarif od skupiny?')) return;
    removing.value = true;
    await router.post(route('groups.detach-tariff'), {
        group_id: groupId,
        tariff_id: tariffId
    }, {
        preserveScroll: true,
        onFinish: () => removing.value = false
    });
};
</script>

<template>
    <div class="bg-gray-50 overflow-hidden shadow-md rounded-xl">
        <table class="table-auto w-full bg-white border border-gray-200 rounded-md">
            <thead>
            <tr class="bg-gray-600 text-blue-50">
                <th class="text-left p-2">Název</th>
<!--                <th class="text-left p-2">Kód / Hodnota</th>-->
                <th class="text-left p-2">Přiřazené služby</th>
                <th class="text-left p-2 w-48"></th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="group in groups" :key="group.id"
                class="border-t odd:bg-gray-50 even:bg-white hover:bg-blue-50 transition">
                <td class="p-2 font-semibold">{{ group.name }}</td>
<!--                <td class="p-2">{{ group.value }}</td>-->
                <td class="p-2">
                <span v-if="group.tariffs && group.tariffs.length">
                    <span v-for="tariff in group.tariffs" :key="tariff.id"
                          class="inline-block bg-gray-200 rounded px-2 py-1 mr-1 mb-1">
                        {{ tariff.name }}
                        <span class="ml-2 text-xs text-gray-500">({{ tariff.pivot.action }})</span>
                        <button type="button"
                                class="ml-1 text-red-500 hover:text-red-700"
                                :disabled="removing"
                                @click="removeTariff(group.id, tariff.id)">
                            &times;
                        </button>
                    </span>
                </span>
                    <span v-else class="text-gray-400">Žádné služby</span>
                </td>
                <td class="px-4 py-2 border-b text-center">
                    <div class="flex flex-nowrap gap-1 justify-center items-center">
                        <SecondaryButton
                            class="bg-blue-500 hover:text-blue-500 text-gray-900 rounded hover:bg-blue-600 shadow hover:shadow-md transition-all duration-150 flex text-xs rounded"
                            @click="$emit('attach-tariff', group)"
                        >+tarif</SecondaryButton>
                        <PrimaryButton
                            class="bg-blue-600 text-xs px-2 py-1 rounded hover:text-blue-500"
                            @click="$emit('edit', group)"
                        >Upravit</PrimaryButton>
                        <DangerButton
                            class="hover:text-red-900 hover:bg-black text-xs px-2 py-1 rounded"
                            @click="$emit('delete', group.id)"
                        >Odstranit</DangerButton>
                    </div>
                </td>
            </tr>
            <tr v-if="!groups.length">
                <td colspan="4" class="p-4 text-center text-gray-500">Žádné skupiny</td>
            </tr>
            </tbody>
        </table>
    </div>
</template>
