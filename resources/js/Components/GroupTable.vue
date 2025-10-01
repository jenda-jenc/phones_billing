<template>
    <div class="bg-gray-50 overflow-hidden shadow-md rounded-xl">
        <table class="table-auto w-full bg-white border border-gray-200 rounded-md">
            <thead>
            <tr class="bg-gray-600 text-blue-50">
                <th class="px-4 py-2 text-left">Název</th>
                <th class="px-4 py-2 text-left">tarif</th>
                <th class="px-4 py-2 text-left">active</th>
            </tr>
            </thead>
            <tbody>
            <tr
                v-for="group in groups"
                :key="group.id"
                class="odd:bg-gray-50 even:bg-white hover:bg-blue-50 transition"
            >
                <td class="px-4 py-2 border-b">{{ group.name }}</td>
                <td class="px-4 py-2 border-b">{{ group.phone }}</td>
                <td class="px-4 py-2 border-b">{{ group.department }}</td>
                <td class="px-4 py-2 border-b">{{ group.limit }}</td>
                <td class="px-4 py-2 border-b text-center">
                    <primary-button
                        class="bg-blue-600 hover:text-blue-500 mr-2"
                        @click="$emit('edit', group)"
                    >
                        &nbsp;&nbsp;Upravit&nbsp;&nbsp;
                    </primary-button>
                    <danger-button
                        class="hover:text-red-500 hover:text-red-900 hover:bg-black"
                        @click="$emit('delete', group.id)"
                    >
                        Odstranit
                    </danger-button>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
</template>

<script setup>
import SecondaryButton from "@/Components/SecondaryButton.vue";
import DangerButton from "@/Components/DangerButton.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";

const props = defineProps({
    groups: {
        type: Array,
        default: () => []
    }
});

const emit = defineEmits(['edit', 'delete']);
</script>
