<template>
    <div v-if="show" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-8 w-full max-w-md shadow-lg relative">
            <button @click="onClose" class="absolute top-2 right-4 text-gray-500 text-xl">&times;</button>
            <h3 class="text-lg font-bold mb-4">Přiřadit službu ke skupině</h3>
            <div class="mb-3">Služba: <b>{{ serviceName }}</b></div>
            <div v-if="!tariffId" class="text-red-600 text-sm mb-2">
                Tarif se stejným názvem nebyl nalezen v DB. Přiřazení není možné.
            </div>
            <select v-model="groupId" class="w-full p-2 border rounded mb-3" :disabled="!tariffId">
                <option disabled value="">Vyberte skupinu</option>
                <option v-for="g in groups" :key="g.id" :value="g.id">
                    {{ g.name }}
                </option>
            </select>
            <select v-model="action" class="w-full p-2 border rounded mb-6" :disabled="!tariffId">
                <option v-for="a in tariffActions" :key="a.value" :value="a.value">{{ a.label }}</option>
            </select>
            <div v-if="error" class="text-red-600 text-sm mb-3">{{ error }}</div>
            <button @click="handleAssign"
                    :disabled="!groupId || !tariffId || loading"
                    class="bg-blue-600 hover:bg-blue-800 text-white px-6 py-2 rounded w-full">
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
    const found = (props.tariffs || []).find(t => t.name === props.serviceName);
    return found ? found.id : '';
});

watch(() => props.show, (v) => {
    if (v) {
        groupId.value = '';
        action.value = props.defaultAction;
    }
});

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
