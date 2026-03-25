<template>
  <div>
    <!-- Page Header -->
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold text-white">Repertorio</h1>
      <button
        v-if="canManage"
        @click="openAddModal"
        class="btn-gold flex items-center gap-2"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Aggiungi Partitura
      </button>
    </div>

    <!-- Filters -->
    <div class="card mb-6">
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <input
          v-model="filters.search"
          type="text"
          class="input-dark"
          placeholder="Cerca titolo o compositore…"
        />
        <input
          v-model="filters.period"
          type="text"
          class="input-dark"
          placeholder="Periodo (es. Barocco, Romantico…)"
        />
        <select v-model="filters.difficulty" class="input-dark">
          <option value="">Tutte le difficoltà</option>
          <option value="Facile">Facile</option>
          <option value="Medio">Medio</option>
          <option value="Difficile">Difficile</option>
        </select>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="flex justify-center py-16">
      <div class="w-10 h-10 border-2 border-dark-border border-t-gold rounded-full animate-spin"></div>
    </div>

    <!-- Error -->
    <div v-else-if="error" class="card border-red-700 text-red-300">
      {{ error }}
    </div>

    <!-- Empty state -->
    <div v-else-if="filtered.length === 0" class="card text-center py-12">
      <svg class="w-12 h-12 text-gray-600 mx-auto mb-3" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path d="M9 3v10.55A4 4 0 1 0 11 17V7h4V3H9zm-2 16a2 2 0 1 1 0-4 2 2 0 0 1 0 4z"/>
      </svg>
      <p class="text-gray-400">Nessuna partitura trovata.</p>
    </div>

    <!-- Table -->
    <div v-else class="card p-0 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="border-b border-dark-border bg-dark-surface">
              <th class="px-6 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Titolo</th>
              <th class="px-6 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Compositore</th>
              <th class="px-6 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider hidden md:table-cell">Periodo</th>
              <th class="px-6 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider hidden sm:table-cell">Difficoltà</th>
              <th class="px-6 py-3 text-right text-xs font-semibold text-gray-400 uppercase tracking-wider">Azioni</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-dark-border">
            <tr
              v-for="score in filtered"
              :key="score.id"
              class="hover:bg-dark-surface/50 transition-colors"
            >
              <td class="px-6 py-4 font-medium text-white">{{ score.title }}</td>
              <td class="px-6 py-4 text-gray-300">{{ score.composer }}</td>
              <td class="px-6 py-4 text-gray-400 hidden md:table-cell">{{ score.period_tag }}</td>
              <td class="px-6 py-4 hidden sm:table-cell">
                <span :class="difficultyClass(score.difficulty_tag)" class="text-xs font-semibold px-2.5 py-0.5 rounded-full">
                  {{ score.difficulty_tag }}
                </span>
              </td>
              <td class="px-6 py-4">
                <div class="flex items-center justify-end gap-2 flex-wrap">
                  <!-- PDF Preview -->
                  <button
                    v-if="score.pdf_path"
                    @click="openPdf(score)"
                    class="btn-ghost text-xs py-1 px-2"
                    title="Anteprima PDF"
                  >
                    Anteprima
                  </button>

                  <!-- Play audio -->
                  <button
                    v-if="score.audio_url"
                    @click="playAudio(score)"
                    class="btn-gold text-xs py-1 px-2"
                    title="Riproduci"
                  >
                    ▶ Play
                  </button>

                  <!-- Share -->
                  <button
                    v-if="score.pdf_path"
                    @click="shareScore(score)"
                    class="btn-ghost text-xs py-1 px-2"
                    title="Condividi"
                  >
                    Invia
                  </button>

                  <!-- Edit (managers only) -->
                  <button
                    v-if="canManage"
                    @click="openEditModal(score)"
                    class="btn-ghost text-xs py-1 px-2"
                    title="Modifica"
                  >
                    Modifica
                  </button>

                  <!-- Delete (managers only) -->
                  <button
                    v-if="canManage"
                    @click="deleteScore(score)"
                    class="text-red-400 hover:text-red-300 text-xs py-1 px-2 border border-transparent hover:border-red-800 rounded-lg transition-colors"
                    title="Elimina"
                  >
                    Elimina
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="pagination.last_page > 1" class="flex items-center justify-between px-6 py-4 border-t border-dark-border">
        <span class="text-xs text-gray-400">Pagina {{ pagination.current_page }} di {{ pagination.last_page }}</span>
        <div class="flex gap-2">
          <button
            :disabled="pagination.current_page === 1"
            @click="changePage(pagination.current_page - 1)"
            class="btn-ghost text-xs py-1 px-3 disabled:opacity-30 disabled:cursor-not-allowed"
          >
            Precedente
          </button>
          <button
            :disabled="pagination.current_page === pagination.last_page"
            @click="changePage(pagination.current_page + 1)"
            class="btn-ghost text-xs py-1 px-3 disabled:opacity-30 disabled:cursor-not-allowed"
          >
            Successiva
          </button>
        </div>
      </div>
    </div>

    <!-- PDF Preview Modal -->
    <Teleport to="body">
      <div
        v-if="pdfModal.show"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm"
        @click.self="pdfModal.show = false"
      >
        <div class="bg-dark-card border border-dark-border rounded-xl w-full max-w-4xl h-[85vh] flex flex-col">
          <div class="flex items-center justify-between p-4 border-b border-dark-border">
            <h3 class="text-white font-semibold">{{ pdfModal.title }}</h3>
            <button @click="pdfModal.show = false" class="text-gray-400 hover:text-white transition-colors">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>
          <iframe
            :src="pdfModal.url"
            class="flex-1 w-full rounded-b-xl"
            title="Anteprima partitura"
          ></iframe>
        </div>
      </div>
    </Teleport>

    <!-- Add/Edit Modal -->
    <DataModal
      :show="dataModal.show"
      :title="dataModal.title"
      :fields="scoreFields"
      :initial-data="dataModal.data"
      @save="saveScore"
      @close="dataModal.show = false"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, inject } from 'vue';
import DataModal from './DataModal.vue';
import { useToast } from '../composables/useToast.js';

const { success, error: toastError } = useToast();

const emit = defineEmits(['play-audio']);
const axios = inject('axios');

const scores = ref([]);
const loading = ref(false);
const error = ref('');
const pagination = ref({ current_page: 1, last_page: 1 });

const filters = ref({ search: '', period: '', difficulty: '' });

const pdfModal = ref({ show: false, url: '', title: '' });
const dataModal = ref({ show: false, title: '', data: null });

const user = computed(() => {
  try { return JSON.parse(localStorage.getItem('auth_user') || 'null'); } catch { return null; }
});
const canManage = computed(() => user.value && ['Admin', 'Direttore'].includes(user.value.role));

const scoreFields = [
  { name: 'title', label: 'Titolo', type: 'text', required: true },
  { name: 'composer', label: 'Compositore', type: 'text', required: true },
  { name: 'period_tag', label: 'Periodo', type: 'text', required: true },
  {
    name: 'difficulty_tag', label: 'Difficoltà', type: 'select', required: true,
    options: [
      { value: 'Facile', label: 'Facile' },
      { value: 'Medio', label: 'Medio' },
      { value: 'Difficile', label: 'Difficile' },
    ],
  },
  { name: 'pdf_path', label: 'URL PDF', type: 'url', required: false },
  { name: 'audio_url', label: 'URL Audio', type: 'url', required: false },
];

const filtered = computed(() => {
  let list = scores.value;
  const s = filters.value.search.toLowerCase();
  const p = filters.value.period.toLowerCase();
  const d = filters.value.difficulty;

  if (s) list = list.filter(r => r.title.toLowerCase().includes(s) || r.composer.toLowerCase().includes(s));
  if (p) list = list.filter(r => r.period_tag.toLowerCase().includes(p));
  if (d) list = list.filter(r => r.difficulty_tag === d);
  return list;
});

function difficultyClass(tag) {
  return {
    Facile: 'bg-green-900 text-green-300',
    Medio: 'bg-amber-900 text-amber-300',
    Difficile: 'bg-red-900 text-red-300',
  }[tag] ?? 'bg-gray-800 text-gray-300';
}

async function fetchScores(page = 1) {
  loading.value = true;
  error.value = '';
  try {
    const { data } = await axios.get('/repertoire', { params: { page } });
    scores.value = data.data;
    pagination.value = { current_page: data.current_page, last_page: data.last_page };
  } catch (e) {
    error.value = 'Impossibile caricare il repertorio.';
  } finally {
    loading.value = false;
  }
}

function changePage(page) { fetchScores(page); }

function openPdf(score) {
  pdfModal.value = { show: true, url: score.pdf_path, title: score.title };
}

function playAudio(score) {
  emit('play-audio', { url: score.audio_url, name: `${score.title} – ${score.composer}` });
}

async function shareScore(score) {
  if (navigator.share) {
    try {
      await navigator.share({ title: score.title, url: score.pdf_path });
    } catch {
      // User cancelled or browser unsupported
    }
  } else {
    await navigator.clipboard.writeText(score.pdf_path);
    success('Link copiato negli appunti!');
  }
}

function openAddModal() {
  dataModal.value = { show: true, title: 'Aggiungi Partitura', data: null };
}

function openEditModal(score) {
  dataModal.value = { show: true, title: 'Modifica Partitura', data: { ...score } };
}

async function saveScore(formData) {
  try {
    if (dataModal.value.data?.id) {
      const { data } = await axios.put(`/repertoire/${dataModal.value.data.id}`, formData);
      const idx = scores.value.findIndex(s => s.id === data.id);
      if (idx >= 0) scores.value[idx] = data;
    } else {
      const { data } = await axios.post('/repertoire', formData);
      scores.value.unshift(data);
    }
    dataModal.value.show = false;
  } catch {
    toastError('Errore nel salvataggio. Verifica i dati.');
  }
}

async function deleteScore(score) {
  if (!confirm(`Eliminare "${score.title}"?`)) return;
  try {
    await axios.delete(`/repertoire/${score.id}`);
    scores.value = scores.value.filter(s => s.id !== score.id);
    success('Partitura eliminata.');
  } catch {
    toastError('Errore durante l\'eliminazione.');
  }
}

onMounted(() => fetchScores());
</script>

<style scoped>
</style>
