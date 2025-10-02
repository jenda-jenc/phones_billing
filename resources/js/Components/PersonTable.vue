<script setup>
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    people: Array,
    groups: Array,
});
const emit = defineEmits(['edit', 'delete', 'attach-group']);

const removing = ref(false);
const search = ref(''); // Proměnná pro text vyhledávání
const searchBy = ref('name'); // Přepínač mezi "name" a "phone"

// Filtrování osob podle nastaveného hledání (name nebo phone)
const normalizedSearch = computed(() => search.value.toLowerCase());

const extractPhones = (person) => {
    if (!person?.phones) {
        return [];
    }

    return person.phones
        .map((entry) => {
            if (entry && typeof entry === 'object') {
                return {
                    phone: entry.phone ?? '',
                    limit: entry.limit ?? null,
                };
            }

            if (typeof entry === 'string') {
                return {
                    phone: entry,
                    limit: null,
                };
            }

            return {
                phone: '',
                limit: null,
            };
        })
        .filter((entry) => entry.phone !== '');
};

const formatLimit = (limit) => {
    if (limit === null || limit === undefined || limit === '') {
        return '—';
    }

    const number = Number(limit);

    if (!Number.isFinite(number)) {
        return '—';
    }

    return number.toLocaleString('cs-CZ', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
};

const formatLimitWithCurrency = (limit) => {
    const formatted = formatLimit(limit);

    return formatted === '—' ? formatted : `${formatted} Kč`;
};

const filteredPeople = computed(() => {
    const query = normalizedSearch.value;

    if (searchBy.value === 'name') {
        return props.people.filter((person) =>
            person.name.toLowerCase().includes(query),
        );
    } else if (searchBy.value === 'phone') {
        return props.people.filter((person) =>
            extractPhones(person).some((entry) =>
                entry.phone.toLowerCase().includes(query),
            ),
        );
    } else if (searchBy.value === 'department') {
        return props.people.filter((person) =>
            person.department
                .toLowerCase()
                .includes(query),
        );
    } else if (searchBy.value === 'group') {
        return props.people.filter(
            (person) =>
                person.groups &&
                person.groups.some((group) =>
                    group.name
                        .toLowerCase()
                        .includes(query),
                ),
        );
    }
    return props.people;
});

const removeGroup = async (personId, groupId) => {
    if (!confirm('Opravdu odebrat tuto skupinu této osobě?')) return;
    removing.value = true;
    await router.post(
        route('persons.detach-group'),
        {
            person_id: personId,
            group_id: groupId,
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
        <!-- Vyhledávací pole a radiobuttony -->
        <div class="space-y-4 p-4">
            <!-- Radiobuttons pro přepínání - samostatný řádek -->
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
                        searchBy === 'name'
                            ? 'Hledejte podle jména'
                            : searchBy === 'phone'
                              ? 'Hledejte podle telefonního čísla'
                              : searchBy === 'department'
                                ? 'Hledejte podle pracovního útvaru'
                                : 'Hledejte podle jména skupiny'
                    "
                    class="w-full rounded border p-2"
                />
            </div>
        </div>

        <!-- Tabulka osob -->
        <table
            class="w-full table-auto rounded-md border border-gray-200 bg-white"
        >
            <thead>
                <tr class="bg-gray-600 text-blue-50">
                    <th class="px-4 py-2 text-left">Jméno</th>
                    <th class="px-4 py-2 text-left">Telefonní čísla</th>
                    <th class="px-4 py-2 text-left">Pracovní útvar</th>
                    <th class="px-4 py-2 text-left">Skupiny</th>
                    <th class="px-4 py-2 text-center">Akce</th>
                </tr>
            </thead>
            <tbody>
                <tr
                    v-for="person in filteredPeople"
                    :key="person.id"
                    class="transition odd:bg-gray-50 even:bg-white hover:bg-blue-50"
                >
                    <td class="border-b px-4 py-2">{{ person.name }}</td>
                    <td class="border-b px-4 py-2">
                        <template v-if="extractPhones(person).length">
                            <div
                                v-for="entry in extractPhones(person)"
                                :key="entry.phone"
                                class="mb-1 flex flex-wrap items-center gap-2"
                            >
                                <span
                                    class="inline-flex items-center rounded bg-blue-100 px-2 py-1 text-sm font-medium text-blue-700"
                                >
                                    {{ entry.phone }}
                                </span>
                                <span class="text-sm text-gray-600">
                                    Limit: {{ formatLimitWithCurrency(entry.limit) }}
                                </span>
                            </div>
                        </template>
                        <span v-else class="text-gray-400">Žádné číslo</span>
                    </td>
                    <td class="border-b px-4 py-2">{{ person.department }}</td>
                    <td class="border-b px-4 py-2">
                        <span v-if="person.groups && person.groups.length">
                            <span
                                v-for="group in person.groups"
                                :key="group.id"
                                class="mb-1 mr-1 inline-block rounded bg-gray-200 px-2 py-1"
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
                    <td class="border-b px-4 py-2 text-center">
                        <div
                            class="flex flex-nowrap items-center justify-center gap-1"
                        >
                            <SecondaryButton
                                class="flex rounded bg-blue-500 text-xs text-gray-900 shadow transition-all duration-150 hover:bg-blue-600 hover:text-blue-500 hover:shadow-md"
                                @click="$emit('attach-group', person)"
                                >+skupina</SecondaryButton
                            >
                            <PrimaryButton
                                class="rounded bg-blue-600 px-2 py-1 text-xs hover:text-blue-500"
                                @click="$emit('edit', person)"
                                >Upravit</PrimaryButton
                            >
                            <DangerButton
                                class="rounded px-2 py-1 text-xs hover:bg-black hover:text-red-900"
                                @click="$emit('delete', person.id)"
                                >Odstranit</DangerButton
                            >
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
