<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage, router } from '@inertiajs/vue3';
import { reactive, ref } from 'vue';
import PersonForm from '../Components/PersonForm.vue';
import PersonTable from '../Components/PersonTable.vue';

const personFormRef = ref(null);
const showForm = ref(false);
const isEditing = ref(false);
const DEFAULT_PHONE_LIMIT = 450;
const formData = reactive({
    id: null,
    name: '',
    phones: [
        {
            phone: '',
            limit: DEFAULT_PHONE_LIMIT,
        },
    ],
    department: '',
});

// Pro přiřazování skupin
const showGroupAssign = ref(false);
const assignPerson = ref(null);
const assignGroupId = ref('');

// Potvrdí přiřazení skupiny (PATCH požadavek na správnou routu)
const confirmAssignGroup = async () => {
    if (!assignPerson.value || !assignGroupId.value) return;
    await router.patch(
        route('persons.assignGroup', assignPerson.value.id),
        { group_id: assignGroupId.value },
        {
            onSuccess: () => {
                showGroupAssign.value = false;
                assignPerson.value = null;
                assignGroupId.value = '';
            },
        },
    );
};

// Otevře/zavře form – pokud form už běží, zruší i editaci a vynuluje data
const toggleForm = () => {
    if (showForm.value) {
        // Zavírám form, vždy zruš editaci i data
        resetForm();
    } else {
        // Otevírám nový form, vynuluj vše
        resetForm();
        showForm.value = true;
        setTimeout(() => {
            personFormRef.value?.$el.querySelector('input')?.focus();
        }, 0);
    }
};

const resetForm = () => {
    formData.id = null;
    formData.name = '';
    formData.phones = [
        {
            phone: '',
            limit: DEFAULT_PHONE_LIMIT,
        },
    ];
    formData.department = '';
    isEditing.value = false;
    showForm.value = false;
};

const handleFormSubmit = async (data) => {
    if (isEditing.value) {
        await router.put(route('persons.update', data.id), data, {
            onSuccess: resetForm,
        });
    } else {
        await router.post(route('persons.store'), data, {
            onSuccess: resetForm,
        });
    }
};

const extractPhones = (person) => {
    if (!person?.phones) {
        return [];
    }

    return person.phones
        .map((entry) => {
            if (typeof entry === 'string') {
                return entry;
            }

            if (entry && typeof entry === 'object') {
                return entry.phone ?? '';
            }

            return '';
        })
        .filter((phone) => phone !== '');
};

// Otevře výběr skupiny pro konkrétní osobu
const attachGroup = (person) => {
    assignPerson.value = {
        ...person,
        phones: extractPhones(person),
    };
    assignGroupId.value = '';
    showGroupAssign.value = true;
    setTimeout(() => {
        personFormRef.value?.$el.querySelector('select')?.focus();
        window.scrollTo({
            top: 0, // Vyjet na začátek stránky
            behavior: 'smooth', // Plynulý přechod
        });
    }, 0);
};
const editPerson = (person) => {
    formData.id = person.id;
    formData.name = person.name;
    formData.department = person.department;
    const phones = Array.isArray(person.phones)
        ? person.phones
              .map((entry) => ({
                  phone:
                      entry && typeof entry === 'object'
                          ? entry.phone ?? ''
                          : typeof entry === 'string'
                            ? entry
                            : '',
                  limit:
                      entry && typeof entry === 'object'
                          ? entry.limit ?? DEFAULT_PHONE_LIMIT
                          : DEFAULT_PHONE_LIMIT,
              }))
              .filter((entry) => entry.phone !== '')
        : [];

    formData.phones = phones.length
        ? phones
        : [
              {
                  phone: '',
                  limit: DEFAULT_PHONE_LIMIT,
              },
          ];
    isEditing.value = true;
    showForm.value = true;
    setTimeout(() => {
        personFormRef.value?.$el.querySelector('input')?.focus();
    }, 0);
};

const deletePerson = async (id) => {
    if (confirm('Opravdu chcete tuto osobu smazat?')) {
        await router.delete(route('persons.destroy', id));
    }
};
</script>

<template>
    <Head title="Seznam Osob" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-2xl font-bold leading-tight text-gray-800">
                Seznam Osob
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <div class="flex justify-end">
                    <button
                        class="flex items-center space-x-2 rounded bg-blue-500 px-6 py-3 text-white shadow transition-all duration-150 hover:bg-blue-600 hover:shadow-md"
                        @click="toggleForm"
                    >
                        <svg
                            v-if="!showForm"
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-5 w-5"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 4v16m8-8H4"
                            />
                        </svg>
                        <svg
                            v-else
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-5 w-5"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"
                            />
                        </svg>
                        <span>{{ showForm ? 'Zavřít' : 'Nová osoba' }}</span>
                    </button>
                </div>

                <div
                    v-if="showForm"
                    class="rounded-lg border border-gray-200 bg-gray-50 p-6 shadow-md"
                >
                    <h3 class="mb-4 text-lg font-semibold text-gray-800">
                        {{ isEditing ? 'Upravit osobu' : 'Přidat novou osobu' }}
                    </h3>
                    <PersonForm
                        ref="personFormRef"
                        :initialData="formData"
                        :errors="usePage().props.errors"
                        :isEditing="isEditing"
                        @submit="handleFormSubmit"
                    />
                </div>

                <!-- Inline assign group select -->
                <div
                    v-if="showGroupAssign"
                    class="my-6 flex items-center gap-4 rounded-lg border border-blue-200 bg-blue-50 p-4"
                >
                    <span
                        >Přiřadit skupinu osobě
                        <b>{{ assignPerson?.name }}</b>
                        <span v-if="assignPerson?.phones?.length"
                            >({{
                                assignPerson?.phones.join(', ')
                            }})</span
                        >:</span
                    >
                    <select
                        v-model="assignGroupId"
                        class="rounded border p-2 px-8"
                    >
                        <option class="" value="" disabled>
                            Vyberte skupinu
                        </option>
                        <option
                            v-for="group in usePage().props.groups"
                            :key="group.id"
                            :value="group.id"
                        >
                            {{ group.name }}
                        </option>
                    </select>
                    <button
                        @click="confirmAssignGroup"
                        class="rounded bg-blue-500 px-4 py-2 text-white hover:bg-blue-600"
                        :disabled="!assignGroupId"
                    >
                        Přiřadit
                    </button>
                    <button
                        @click="showGroupAssign = false"
                        class="rounded bg-red-600 px-4 py-2 text-white hover:bg-red-500 hover:text-red-900"
                    >
                        Zrušit
                    </button>
                </div>

                <div
                    class="rounded-lg border border-gray-200 bg-white p-6 shadow-md"
                >
                    <h3 class="mb-4 text-lg font-semibold text-gray-800">
                        Seznam osob
                    </h3>
                    <PersonTable
                        :people="usePage().props.people"
                        :groups="usePage().props.groups"
                        @edit="editPerson"
                        @delete="deletePerson"
                        @attach-group="attachGroup"
                    />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
