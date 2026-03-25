<template>
  <div>
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold text-white">Eventi</h1>
      <button v-if="canManage" @click="openAddModal" class="btn-gold flex items-center gap-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Aggiungi Evento
      </button>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="flex justify-center py-16">
      <div class="w-10 h-10 border-2 border-dark-border border-t-gold rounded-full animate-spin"></div>
    </div>

    <!-- Error -->
    <div v-else-if="error" class="card border-red-700 text-red-300">{{ error }}</div>

    <!-- Empty -->
    <div v-else-if="events.length === 0" class="card text-center py-12">
      <svg class="w-12 h-12 text-gray-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
      </svg>
      <p class="text-gray-400">Nessun evento in programma.</p>
    </div>

    <!-- Event Cards Grid -->
    <div v-else class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
      <div
        v-for="event in events"
        :key="event.id"
        class="card flex flex-col gap-4"
      >
        <!-- Card Header -->
        <div class="flex items-start justify-between gap-2">
          <div>
            <h3 class="text-white font-semibold text-lg leading-tight">{{ event.title }}</h3>
            <p class="text-gray-400 text-sm mt-1">{{ event.location }}</p>
          </div>
          <span :class="event.type === 'Concerto' ? 'badge-concerto' : 'badge-prova'" class="flex-shrink-0 mt-0.5">
            {{ event.type }}
          </span>
        </div>

        <!-- Date & Time -->
        <div class="flex items-center gap-4 text-sm">
          <div class="flex items-center gap-1.5 text-gray-300">
            <svg class="w-4 h-4 text-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            {{ formatDate(event.event_date) }}
          </div>
          <div class="flex items-center gap-1.5 text-gray-300">
            <svg class="w-4 h-4 text-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ event.event_time }}
          </div>
        </div>

        <!-- Description -->
        <p v-if="event.description" class="text-gray-400 text-sm line-clamp-2">{{ event.description }}</p>

        <!-- Attendance -->
        <div class="pt-3 border-t border-dark-border">
          <p class="text-xs text-gray-500 mb-2 uppercase tracking-wider font-semibold">La mia presenza</p>
          <div class="flex items-center gap-2">
            <select
              v-model="attendanceStatus[event.id]"
              @change="markAttendance(event.id)"
              class="input-dark text-sm py-1.5 flex-1"
            >
              <option value="">— Seleziona —</option>
              <option value="Presente">✓ Presente</option>
              <option value="Assente">✗ Assente</option>
              <option value="Giustificato">~ Giustificato</option>
            </select>
            <span v-if="attendanceSaved[event.id]" class="text-green-400 text-xs">✓</span>
          </div>
        </div>

        <!-- Actions (managers) -->
        <div v-if="canManage" class="flex gap-2 pt-2">
          <button @click="openEditModal(event)" class="btn-ghost text-xs py-1 px-3 flex-1">
            Modifica
          </button>
          <button @click="deleteEvent(event)" class="text-red-400 hover:text-red-300 text-xs py-1 px-3 border border-transparent hover:border-red-800 rounded-lg transition-colors">
            Elimina
          </button>
        </div>
      </div>
    </div>

    <!-- Pagination -->
    <div v-if="pagination.last_page > 1" class="flex items-center justify-between mt-6">
      <span class="text-xs text-gray-400">Pagina {{ pagination.current_page }} di {{ pagination.last_page }}</span>
      <div class="flex gap-2">
        <button :disabled="pagination.current_page === 1" @click="changePage(pagination.current_page - 1)" class="btn-ghost text-xs py-1 px-3 disabled:opacity-30 disabled:cursor-not-allowed">Precedente</button>
        <button :disabled="pagination.current_page === pagination.last_page" @click="changePage(pagination.current_page + 1)" class="btn-ghost text-xs py-1 px-3 disabled:opacity-30 disabled:cursor-not-allowed">Successiva</button>
      </div>
    </div>

    <!-- Add/Edit Modal -->
    <DataModal
      :show="dataModal.show"
      :title="dataModal.title"
      :fields="eventFields"
      :initial-data="dataModal.data"
      @save="saveEvent"
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

const events = ref([]);
const loading = ref(false);
const error = ref('');
const pagination = ref({ current_page: 1, last_page: 1 });
const attendanceStatus = ref({});
const attendanceSaved = ref({});
const dataModal = ref({ show: false, title: '', data: null });

const user = computed(() => {
  try { return JSON.parse(localStorage.getItem('auth_user') || 'null'); } catch { return null; }
});
const canManage = computed(() => user.value && ['Admin', 'Direttore'].includes(user.value.role));

const eventFields = [
  { name: 'title', label: 'Titolo', type: 'text', required: true },
  { name: 'event_date', label: 'Data', type: 'date', required: true },
  { name: 'event_time', label: 'Ora', type: 'time', required: true },
  { name: 'location', label: 'Luogo', type: 'text', required: true },
  {
    name: 'type', label: 'Tipo', type: 'select', required: true,
    options: [{ value: 'Prova', label: 'Prova' }, { value: 'Concerto', label: 'Concerto' }],
  },
  { name: 'description', label: 'Descrizione', type: 'textarea', required: false },
];

function formatDate(dateStr) {
  return new Date(dateStr).toLocaleDateString('it-IT', { day: '2-digit', month: 'long', year: 'numeric' });
}

async function fetchEvents(page = 1) {
  loading.value = true;
  error.value = '';
  try {
    const { data } = await axios.get('/events', { params: { page } });
    events.value = data.data;
    pagination.value = { current_page: data.current_page, last_page: data.last_page };
  } catch {
    error.value = 'Impossibile caricare gli eventi.';
  } finally {
    loading.value = false;
  }
}

function changePage(page) { fetchEvents(page); }

async function markAttendance(eventId) {
  const status = attendanceStatus.value[eventId];
  if (!status) return;
  try {
    await axios.post(`/events/${eventId}/attendance`, { status });
    attendanceSaved.value[eventId] = true;
    setTimeout(() => { attendanceSaved.value[eventId] = false; }, 2000);
  } catch {
    toastError('Errore nel salvataggio della presenza.');
  }
}

function openAddModal() {
  dataModal.value = { show: true, title: 'Aggiungi Evento', data: null };
}

function openEditModal(event) {
  dataModal.value = { show: true, title: 'Modifica Evento', data: { ...event } };
}

async function saveEvent(formData) {
  try {
    if (dataModal.value.data?.id) {
      const { data } = await axios.put(`/events/${dataModal.value.data.id}`, formData);
      const idx = events.value.findIndex(e => e.id === data.id);
      if (idx >= 0) events.value[idx] = data;
    } else {
      const { data } = await axios.post('/events', formData);
      events.value.unshift(data);
    }
    dataModal.value.show = false;
    success('Evento salvato.');
  } catch {
    toastError('Errore nel salvataggio. Verifica i dati.');
  }
}

async function deleteEvent(event) {
  if (!confirm(`Eliminare l'evento "${event.title}"?`)) return;
  try {
    await axios.delete(`/events/${event.id}`);
    events.value = events.value.filter(e => e.id !== event.id);
    success('Evento eliminato.');
  } catch {
    toastError('Errore durante l\'eliminazione.');
  }
}

onMounted(() => fetchEvents());
</script>

<style scoped>
</style>
