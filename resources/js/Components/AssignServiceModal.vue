<template>
    <div
        v-if="show"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40"
    >
        <div class="relative w-full max-w-md rounded-lg bg-white p-8 shadow-lg">
            <button
                @click="onClose"
                class="absolute right-4 top-2 text-xl text-gray-500"
            >
                &times;
            </button>
            <h3 class="mb-4 text-lg font-bold">Přiřadit službu ke skupině</h3>
            <div class="mb-3">
                Služba: <b>{{ serviceName }}</b>
            </div>
            <div v-if="!tariffId" class="mb-2 text-sm text-red-600">
                Tarif se stejným názvem nebyl nalezen v DB. Přiřazení není
                možné.
            </div>
            <select
                v-model="groupId"
                class="mb-3 w-full rounded border p-2"
                :disabled="!tariffId"
            >
                <option disabled value="">Vyberte skupinu</option>
                <option v-for="g in groups" :key="g.id" :value="g.id">
                    {{ g.name }}
                </option>
            </select>
            <select
                v-model="action"
                class="mb-6 w-full rounded border p-2"
                :disabled="!tariffId"
            >
                <option
                    v-for="a in tariffActions"
                    :key="a.value"
                    :value="a.value"
                >
                    {{ a.label }}
                </option>
            </select>
            <div v-if="error" class="mb-3 text-sm text-red-600">
                {{ error }}
            </div>
            <button
                @click="handleAssign"
                :disabled="!groupId || !tariffId || loading"
                class="w-full rounded bg-blue-600 px-6 py-2 text-white hover:bg-blue-800"
            >
                {{ loading ? 'Přiřazuji...' : 'Přiřadit' }}
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref, watch, computed } from 'vue';

const props = defineProps({
    show: Boolean,
    serviceName: String,
    groups: Array,
    tariffs: Array,
    loading: Boolean,
    error: String,
    defaultAction: { type: String, default: 'plati_sam' },
});

const emit = defineEmits(['close', 'assign']);

const groupId = ref('');
const action = ref(props.defaultAction);

const tariffActions = [
    { value: 'ignorovat', label: 'Ignorovat' },
    { value: 'plati_sam', label: 'Platí sám' },
    { value: 'do_limitu', label: 'Do limitu' },
];

// Automaticky vybere tarifId (dle jména)
const tariffId = computed(() => {
    const found = (props.tariffs || []).find(
        (t) => t.name === props.serviceName,
    );
    return found ? found.id : '';
});

watch(
    () => props.show,
    (v) => {
        if (v) {
            groupId.value = '';
            action.value = props.defaultAction;
        }
    },
);

function handleAssign() {
    emit('assign', {
        groupId: groupId.value,
        tariffId: tariffId.value,
        action: action.value,
    });
}

function onClose() {
    emit('close');
}
</script>
