<template>
    <div class="overflow-hidden rounded-xl bg-gray-50 shadow-md">
        <table
            class="w-full table-auto rounded-md border border-gray-200 bg-white"
        >
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
                    class="transition odd:bg-gray-50 even:bg-white hover:bg-blue-50"
                >
                    <td class="border-b px-4 py-2">{{ group.name }}</td>
                    <td class="border-b px-4 py-2">{{ group.phone }}</td>
                    <td class="border-b px-4 py-2">{{ group.department }}</td>
                    <td class="border-b px-4 py-2">{{ group.limit }}</td>
                    <td class="border-b px-4 py-2 text-center">
                        <primary-button
                            class="mr-2 bg-blue-600 hover:text-blue-500"
                            @click="$emit('edit', group)"
                        >
                            &nbsp;&nbsp;Upravit&nbsp;&nbsp;
                        </primary-button>
                        <danger-button
                            class="hover:bg-black hover:text-red-500 hover:text-red-900"
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
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    groups: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(['edit', 'delete']);
</script>
