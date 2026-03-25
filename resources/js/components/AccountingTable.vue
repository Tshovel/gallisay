<template>
  <div>
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold text-white">Contabilità</h1>
      <button v-if="isAdmin" @click="openAddModal" class="btn-gold flex items-center gap-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Aggiungi Pagamento
      </button>
    </div>

    <!-- Summary cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
      <div class="card">
        <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Totale</p>
        <p class="text-2xl font-bold text-white">{{ formatCurrency(totals.all) }}</p>
      </div>
      <div class="card">
        <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Pagato</p>
        <p class="text-2xl font-bold text-green-400">{{ formatCurrency(totals.pagato) }}</p>
      </div>
      <div class="card">
        <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">In Attesa</p>
        <p class="text-2xl font-bold text-amber-400">{{ formatCurrency(totals.inAttesa) }}</p>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="flex justify-center py-16">
      <div class="w-10 h-10 border-2 border-dark-border border-t-gold rounded-full animate-spin"></div>
    </div>

    <!-- Error -->
    <div v-else-if="error" class="card border-red-700 text-red-300">{{ error }}</div>

    <!-- Empty -->
    <div v-else-if="records.length === 0" class="card text-center py-12">
      <p class="text-gray-400">Nessuna voce contabile trovata.</p>
    </div>

    <!-- Table -->
    <div v-else class="card p-0 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="border-b border-dark-border bg-dark-surface">
              <th class="px-6 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Utente</th>
              <th class="px-6 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Descrizione</th>
              <th class="px-6 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider hidden sm:table-cell">Data</th>
              <th class="px-6 py-3 text-right text-xs font-semibold text-gray-400 uppercase tracking-wider">Importo</th>
              <th class="px-6 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Stato</th>
              <th v-if="isAdmin" class="px-6 py-3 text-right text-xs font-semibold text-gray-400 uppercase tracking-wider">Azioni</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-dark-border">
            <tr
              v-for="record in records"
              :key="record.id"
              class="hover:bg-dark-surface/50 transition-colors"
            >
              <td class="px-6 py-4 text-white font-medium">
                {{ record.user ? `${record.user.first_name} ${record.user.last_name}` : '—' }}
              </td>
              <td class="px-6 py-4 text-gray-300">{{ record.description }}</td>
              <td class="px-6 py-4 text-gray-400 hidden sm:table-cell">
                {{ record.payment_date ? formatDate(record.payment_date) : '—' }}
              </td>
              <td class="px-6 py-4 text-right font-semibold" :class="record.status === 'Pagato' ? 'text-green-400' : 'text-amber-400'">
                {{ formatCurrency(record.amount) }}
              </td>
              <td class="px-6 py-4">
                <span :class="record.status === 'Pagato' ? 'badge-pagato' : 'badge-attesa'">
                  {{ record.status }}
                </span>
              </td>
              <td v-if="isAdmin" class="px-6 py-4 text-right">
                <button @click="openEditModal(record)" class="btn-ghost text-xs py-1 px-2">
                  Modifica
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="pagination.last_page > 1" class="flex items-center justify-between px-6 py-4 border-t border-dark-border">
        <span class="text-xs text-gray-400">Pagina {{ pagination.current_page }} di {{ pagination.last_page }}</span>
        <div class="flex gap-2">
          <button :disabled="pagination.current_page === 1" @click="changePage(pagination.current_page - 1)" class="btn-ghost text-xs py-1 px-3 disabled:opacity-30 disabled:cursor-not-allowed">Precedente</button>
          <button :disabled="pagination.current_page === pagination.last_page" @click="changePage(pagination.current_page + 1)" class="btn-ghost text-xs py-1 px-3 disabled:opacity-30 disabled:cursor-not-allowed">Successiva</button>
        </div>
      </div>
    </div>

    <!-- Add/Edit Modal -->
    <DataModal
      :show="dataModal.show"
      :title="dataModal.title"
      :fields="accountingFields"
      :initial-data="dataModal.data"
      @save="saveRecord"
      @close="dataModal.show = false"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, inject } from 'vue';
import DataModal from './DataModal.vue';
import { useToast } from '../composables/useToast.js';

const { success, error: toastError } = useToast();

const axios = inject('axios');

const records = ref([]);
const loading = ref(false);
const error = ref('');
const pagination = ref({ current_page: 1, last_page: 1 });
const dataModal = ref({ show: false, title: '', data: null });

const user = computed(() => {
  try { return JSON.parse(localStorage.getItem('auth_user') || 'null'); } catch { return null; }
});
const isAdmin = computed(() => user.value?.role === 'Admin');

const totals = computed(() => {
  const all = records.value.reduce((sum, r) => sum + parseFloat(r.amount || 0), 0);
  const pagato = records.value.filter(r => r.status === 'Pagato').reduce((sum, r) => sum + parseFloat(r.amount || 0), 0);
  const inAttesa = records.value.filter(r => r.status === 'In attesa').reduce((sum, r) => sum + parseFloat(r.amount || 0), 0);
  return { all, pagato, inAttesa };
});

const accountingFields = [
  { name: 'user_id', label: 'ID Utente', type: 'number', required: true },
  { name: 'description', label: 'Descrizione', type: 'text', required: true },
  { name: 'amount', label: 'Importo (€)', type: 'number', required: true, step: '0.01' },
  { name: 'payment_date', label: 'Data Pagamento', type: 'date', required: false },
  {
    name: 'status', label: 'Stato', type: 'select', required: true,
    options: [{ value: 'Pagato', label: 'Pagato' }, { value: 'In attesa', label: 'In attesa' }],
  },
];

function formatCurrency(value) {
  return new Intl.NumberFormat('it-IT', { style: 'currency', currency: 'EUR' }).format(value || 0);
}

function formatDate(dateStr) {
  return new Date(dateStr).toLocaleDateString('it-IT');
}

async function fetchRecords(page = 1) {
  loading.value = true;
  error.value = '';
  try {
    const { data } = await axios.get('/accounting', { params: { page } });
    records.value = data.data;
    pagination.value = { current_page: data.current_page, last_page: data.last_page };
  } catch {
    error.value = 'Impossibile caricare i dati contabili.';
  } finally {
    loading.value = false;
  }
}

function changePage(page) { fetchRecords(page); }

function openAddModal() {
  dataModal.value = { show: true, title: 'Aggiungi Pagamento', data: null };
}

function openEditModal(record) {
  dataModal.value = { show: true, title: 'Modifica Pagamento', data: { ...record, user_id: record.user_id } };
}

async function saveRecord(formData) {
  try {
    if (dataModal.value.data?.id) {
      const { data } = await axios.put(`/accounting/${dataModal.value.data.id}`, formData);
      const idx = records.value.findIndex(r => r.id === data.id);
      if (idx >= 0) records.value[idx] = data;
    } else {
      const { data } = await axios.post('/accounting', formData);
      records.value.unshift(data);
    }
    dataModal.value.show = false;
    success('Pagamento salvato.');
  } catch {
    toastError('Errore nel salvataggio. Verifica i dati.');
  }
}

onMounted(() => fetchRecords());
</script>

<style scoped>
</style>
