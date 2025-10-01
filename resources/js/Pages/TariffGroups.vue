<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage, router } from '@inertiajs/vue3';
import TariffGroupTable from '../Components/TariffGroupTable.vue';
import TariffGroupForm from '../Components/TariffGroupForm.vue';
import TariffGroupTariffModal from '../Components/TariffGroupTariffModal.vue';
import { reactive, ref } from 'vue';

const groups = usePage().props.groups;
const tariffs = usePage().props.tariffs;

const groupFormRef = ref(null);
const showForm = ref(false);
const isEditing = ref(false);

const formData = reactive({
    id: null,
    name: '',
    value: '',
});

const showTariffModal = ref(false);
const selectedGroup = ref(null);

// Nová logika toggleForm (jako u osob)
const toggleForm = () => {
    if (showForm.value) {
        resetForm(); // Zavře form a vynuluje i editaci
    } else {
        resetForm(); // Otevřít nový prázdný form
        showForm.value = true;
        setTimeout(() => {
            groupFormRef.value?.$el.querySelector('input')?.focus();
        }, 0);
    }
};

const resetForm = () => {
    formData.id = null;
    formData.name = '';
    formData.value = '';
    isEditing.value = false;
    showForm.value = false;
};

const handleFormSubmit = async (data) => {
    if (isEditing.value) {
        await router.put(route('groups.update', data.id), data, {
            onSuccess: resetForm,
        });
    } else {
        await router.post(route('groups.store'), data, {
            onSuccess: resetForm,
        });
    }
};

const editGroup = (group) => {
    resetForm();
    setTimeout(() => {
        Object.assign(formData, group);
        isEditing.value = true;
        showForm.value = true;
        setTimeout(() => {
            groupFormRef.value?.$el.querySelector('input')?.focus();
        }, 0);
    }, 0);
};

const deleteGroup = async (id) => {
    if (confirm('Opravdu chcete tuto skupinu smazat?')) {
        await router.delete(route('groups.destroy', id));
    }
};

const openTariffModal = (group) => {
    selectedGroup.value = group;
    showTariffModal.value = true;
};
</script>

<template>
    <Head title="Správa skupin tarifů" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-2xl font-bold leading-tight text-gray-800">
                Správa skupin služeb
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
                        <span>{{ showForm ? 'Zavřít' : 'Nová skupina' }}</span>
                    </button>
                </div>

                <div
                    v-if="showForm"
                    class="rounded-lg border border-gray-200 bg-gray-50 p-6 shadow-md"
                >
                    <h3 class="mb-4 text-lg font-semibold text-gray-800">
                        {{
                            isEditing
                                ? 'Upravit skupinu'
                                : 'Přidat novou skupinu'
                        }}
                    </h3>
                    <TariffGroupForm
                        ref="groupFormRef"
                        :initialData="formData"
                        :errors="usePage().props.errors"
                        :isEditing="isEditing"
                        @submit="handleFormSubmit"
                    />
                </div>

                <div
                    class="rounded-lg border border-gray-200 bg-white p-6 shadow-md"
                >
                    <h3 class="mb-4 text-lg font-semibold text-gray-800">
                        Seznam skupin služeb
                    </h3>
                    <TariffGroupTable
                        :groups="usePage().props.groups"
                        @attach-tariff="openTariffModal"
                        @edit="editGroup"
                        @delete="deleteGroup"
                    />
                </div>
            </div>
        </div>

        <TariffGroupTariffModal
            v-if="showTariffModal"
            :group="selectedGroup"
            :tariffs="tariffs"
            @close="showTariffModal = false"
        />
    </AuthenticatedLayout>
</template>
