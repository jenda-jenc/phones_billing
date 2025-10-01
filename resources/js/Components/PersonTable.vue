<script setup>
import SecondaryButton from "@/Components/SecondaryButton.vue";
import DangerButton from "@/Components/DangerButton.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import {router} from '@inertiajs/vue3';
import {ref, computed} from 'vue';

const props = defineProps({
    people: Array,
    groups: Array,
});
const emit = defineEmits(['edit', 'delete', 'attach-group']);

const removing = ref(false);
const search = ref(''); // Proměnná pro text vyhledávání
const searchBy = ref('name'); // Přepínač mezi "name" a "phone"

// Filtrování osob podle nastaveného hledání (name nebo phone)
const filteredPeople = computed(() => {
    if (searchBy.value === 'name') {
        return props.people.filter(person =>
            person.name.toLowerCase().includes(search.value.toLowerCase())
        );
    } else if (searchBy.value === 'phone') {
        return props.people.filter(person =>
            person.phone.toLowerCase().includes(search.value.toLowerCase())
        );
    } else if (searchBy.value === 'department') {
        return props.people.filter(person =>
            person.department.toLowerCase().includes(search.value.toLowerCase())
        );
    } else if (searchBy.value === 'group') {
        return props.people.filter(person =>
                person.groups && person.groups.some(group =>
                    group.name.toLowerCase().includes(search.value.toLowerCase())
                )
        );
    }
    return props.people;
});

const removeGroup = async (personId, groupId) => {
    if (!confirm('Opravdu odebrat tuto skupinu této osobě?')) return;
    removing.value = true;
    await router.post(route('persons.detach-group'), {
        person_id: personId,
        group_id: groupId
    }, {
        preserveScroll: true,
        onFinish: () => removing.value = false
    });
};
</script>
<template>
    <div class="bg-gray-50 overflow-hidden shadow-md rounded-xl">
        <!-- Vyhledávací pole a radiobuttony -->
        <div class="p-4 space-y-4">
            <!-- Radiobuttons pro přepínání - samostatný řádek -->
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
                <label class="flex items-center gap-2">
                    <input
                        type="radio"
                        name="searchBy"
                        value="department"
                        v-model="searchBy"
                        class="form-radio text-blue-600"
                    />
                    <span>Pracovní útvar</span>
                </label>
                <label class="flex items-center gap-2">
                    <input
                        type="radio"
                        name="searchBy"
                        value="group"
                        v-model="searchBy"
                        class="form-radio text-blue-600"
                    />
                    <span>Skupina</span>
                </label>
            </div>

            <!-- Hledací pole -->
            <div>
                <input
                    type="text"
                    v-model="search"
                    :placeholder="
                        searchBy === 'name' ? 'Hledejte podle jména' :
                        searchBy === 'phone' ? 'Hledejte podle telefonního čísla' :
                        searchBy === 'department' ? 'Hledejte podle pracovního útvaru' :
                        'Hledejte podle jména skupiny'
                    "
                    class="w-full p-2 border rounded"
                />
            </div>
        </div>

        <!-- Tabulka osob -->
        <table class="table-auto w-full bg-white border border-gray-200 rounded-md">
            <thead>
            <tr class="bg-gray-600 text-blue-50">
                <th class="px-4 py-2 text-left">Jméno</th>
                <th class="px-4 py-2 text-left">Telefonní číslo</th>
                <th class="px-4 py-2 text-left">Pracovní útvar</th>
                <th class="px-4 py-2 text-left">Limit</th>
                <th class="px-4 py-2 text-left">Skupiny</th>
                <th class="px-4 py-2 text-center">Akce</th>
            </tr>
            </thead>
            <tbody>
            <tr
                v-for="person in filteredPeople"
                :key="person.id"
                class="odd:bg-gray-50 even:bg-white hover:bg-blue-50 transition"
            >
                <td class="px-4 py-2 border-b">{{ person.name }}</td>
                <td class="px-4 py-2 border-b">{{ person.phone }}</td>
                <td class="px-4 py-2 border-b">{{ person.department }}</td>
                <td class="px-4 py-2 border-b">{{ person.limit }}</td>
                <td class="px-4 py-2 border-b">
                    <span v-if="person.groups && person.groups.length">
                        <span
                            v-for="group in person.groups"
                            :key="group.id"
                            class="inline-block bg-gray-200 rounded px-2 py-1 mr-1 mb-1"
                        >
                            {{ group.name }}
                            <button
                                type="button"
                                class="ml-1 text-red-500 hover:text-red-700"
                                :disabled="removing"
                                @click="removeGroup(person.id, group.id)"
                            >
                                &times;
                            </button>
                        </span>
                    </span>
                    <span v-else class="text-gray-400">Žádná skupina</span>
                </td>
                <td class="px-4 py-2 border-b text-center">
                    <div class="flex flex-nowrap gap-1 justify-center items-center">
                        <SecondaryButton
                            class="bg-blue-500 hover:text-blue-500 text-gray-900 rounded hover:bg-blue-600 shadow hover:shadow-md transition-all duration-150 flex text-xs rounded"
                            @click="$emit('attach-group', person)"
                        >+skupina</SecondaryButton>
                        <PrimaryButton
                            class="bg-blue-600 text-xs px-2 py-1 rounded hover:text-blue-500"
                            @click="$emit('edit', person)"
                        >Upravit</PrimaryButton>
                        <DangerButton
                            class="hover:text-red-900 hover:bg-black text-xs px-2 py-1 rounded"
                            @click="$emit('delete', person.id)"
                        >Odstranit</DangerButton>
                    </div>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
</template>
