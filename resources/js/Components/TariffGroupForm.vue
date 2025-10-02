<script setup>
import { ref, reactive, watch } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    initialData: Object,
    errors: Object,
    isEditing: Boolean,
});
const emit = defineEmits(['submit']);

// Reaktivní kopie vstupních dat
const localData = reactive({
    id: null,
    name: '',
    value: '',
    tariffs: [],
});

// Aktualizuj localData při změně initialData
watch(
    () => props.initialData,
    (val) => {
        Object.assign(
            localData,
            val || { id: null, name: '', value: '', tariffs: [] },
        );
    },
    { immediate: true },
);

// Odebrání tarifu od skupiny
const removing = ref(false);
const removeTariff = async (tariffId) => {
    if (!localData.id) return;
    if (!confirm('Opravdu odebrat tento tarif od skupiny?')) return;
    removing.value = true;
    await router.post(
        route('groups.detach-tariff'),
        {
            group_id: localData.id,
            tariff_id: tariffId,
        },
        {
            onSuccess: () => {
                // Odebereme tarif z localData bez reloadu (frontendově)
                localData.tariffs = localData.tariffs.filter(
                    (t) => t.id !== tariffId,
                );
            },
            onFinish: () => (removing.value = false),
        },
    );
};

const submit = () => {
    emit('submit', { ...localData });
};
</script>

<template>
    <form @submit.prevent="submit" class="space-y-4">
        <div>
            <label class="block text-sm font-medium">Název skupiny</label>
            <input
                v-model="localData.name"
                type="text"
                :class="[
                    'w-full rounded border px-3 py-2',
                    errors && errors.name ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : 'border-gray-300 focus:border-blue-500 focus:ring-blue-500',
                ]"
            />
            <div v-if="errors && errors.name" class="text-xs text-red-600">
                {{ errors.name }}
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium"
                >Kód / hodnota (unikátní)</label
            >
            <input
                v-model="localData.value"
                type="text"
                :class="[
                    'w-full rounded border px-3 py-2',
                    errors && errors.value ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : 'border-gray-300 focus:border-blue-500 focus:ring-blue-500',
                ]"
            />
            <div v-if="errors && errors.value" class="text-xs text-red-600">
                {{ errors.value }}
            </div>
        </div>

        <!--        &lt;!&ndash; Výpis tarifů při editaci &ndash;&gt;-->
        <!--        <div v-if="props.isEditing && localData.tariffs && localData.tariffs.length" class="mb-2">-->
        <!--            <label class="block text-sm font-medium mb-1">Přiřazené tarify:</label>-->
        <!--            <div class="flex flex-wrap gap-2">-->
        <!--                <span v-for="tariff in localData.tariffs" :key="tariff.id"-->
        <!--                      class="inline-flex items-center bg-gray-200 rounded px-2 py-1">-->
        <!--                    {{ tariff.name }}-->
        <!--                    <span class="ml-2 text-xs text-gray-500">({{ tariff.pivot.action }})</span>-->
        <!--                    <button type="button"-->
        <!--                            class="ml-1 text-red-500 hover:text-red-700"-->
        <!--                            :disabled="removing"-->
        <!--                            @click="removeTariff(tariff.id)">-->
        <!--                        &times;-->
        <!--                    </button>-->
        <!--                </span>-->
        <!--            </div>-->
        <!--        </div>-->

        <div class="flex justify-end">
            <button
                type="submit"
                class="rounded bg-blue-500 px-6 py-2 text-white hover:bg-blue-600"
            >
                {{ isEditing ? 'Uložit změny' : 'Přidat skupinu' }}
            </button>
        </div>
    </form>
</template>
