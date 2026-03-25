<template>
  <div>
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold text-white">Dashboard</h1>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="flex justify-center py-16">
      <div class="w-10 h-10 border-2 border-dark-border border-t-gold rounded-full animate-spin"></div>
    </div>

    <div v-else>
      <!-- Stats Row -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <!-- Payment donut chart -->
        <div class="card">
          <h2 class="text-lg font-semibold text-white mb-4">Stato Pagamenti</h2>
          <div class="relative" style="height: 260px;">
            <canvas ref="paymentChartEl" aria-label="Grafico stato pagamenti"></canvas>
          </div>
          <div class="flex justify-center gap-6 mt-4 text-sm">
            <div class="flex items-center gap-2">
              <span class="w-3 h-3 rounded-full bg-gold inline-block"></span>
              <span class="text-gray-300">Pagato: <strong class="text-white">{{ stats.payments?.pagato ?? 0 }}</strong></span>
            </div>
            <div class="flex items-center gap-2">
              <span class="w-3 h-3 rounded-full bg-dark-border inline-block"></span>
              <span class="text-gray-300">In attesa: <strong class="text-white">{{ stats.payments?.in_attesa ?? 0 }}</strong></span>
            </div>
          </div>
        </div>

        <!-- Attendance bar chart -->
        <div class="card">
          <h2 class="text-lg font-semibold text-white mb-4">Presenze Ultime 5 Prove</h2>
          <div class="relative" style="height: 260px;">
            <canvas ref="attendanceChartEl" aria-label="Grafico presenze"></canvas>
          </div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="card">
        <h2 class="text-lg font-semibold text-white mb-4">Azioni Rapide</h2>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
          <button @click="quickAction('event')" class="btn-gold flex items-center justify-center gap-2 py-3">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            Aggiungi Evento
          </button>
          <button @click="quickAction('score')" class="btn-gold flex items-center justify-center gap-2 py-3">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
              <path d="M9 3v10.55A4 4 0 1 0 11 17V7h4V3H9zm-2 16a2 2 0 1 1 0-4 2 2 0 0 1 0 4z"/>
            </svg>
            Aggiungi Partitura
          </button>
          <button @click="quickAction('payment')" class="btn-gold flex items-center justify-center gap-2 py-3">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z"/>
            </svg>
            Aggiungi Pagamento
          </button>
        </div>
      </div>
    </div>

    <!-- Quick-action Modals -->
    <DataModal
      :show="eventModal.show"
      title="Aggiungi Evento"
      :fields="eventFields"
      :initial-data="null"
      @save="saveEvent"
      @close="eventModal.show = false"
    />
    <DataModal
      :show="scoreModal.show"
      title="Aggiungi Partitura"
      :fields="scoreFields"
      :initial-data="null"
      @save="saveScore"
      @close="scoreModal.show = false"
    />
    <DataModal
      :show="paymentModal.show"
      title="Aggiungi Pagamento"
      :fields="paymentFields"
      :initial-data="null"
      @save="savePayment"
      @close="paymentModal.show = false"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, inject, nextTick } from 'vue';
import DataModal from './DataModal.vue';
import { useToast } from '../composables/useToast.js';

const { error: toastError } = useToast();

const axios = inject('axios');

const stats = ref({ payments: { pagato: 0, in_attesa: 0 }, attendance: [] });
const loading = ref(true);

const paymentChartEl = ref(null);
const attendanceChartEl = ref(null);

const eventModal = ref({ show: false });
const scoreModal = ref({ show: false });
const paymentModal = ref({ show: false });

const eventFields = [
  { name: 'title', label: 'Titolo', type: 'text', required: true },
  { name: 'event_date', label: 'Data', type: 'date', required: true },
  { name: 'event_time', label: 'Ora', type: 'time', required: true },
  { name: 'location', label: 'Luogo', type: 'text', required: true },
  { name: 'type', label: 'Tipo', type: 'select', required: true, options: [{ value: 'Prova', label: 'Prova' }, { value: 'Concerto', label: 'Concerto' }] },
  { name: 'description', label: 'Descrizione', type: 'textarea', required: false },
];

const scoreFields = [
  { name: 'title', label: 'Titolo', type: 'text', required: true },
  { name: 'composer', label: 'Compositore', type: 'text', required: true },
  { name: 'period_tag', label: 'Periodo', type: 'text', required: true },
  { name: 'difficulty_tag', label: 'Difficoltà', type: 'select', required: true, options: [{ value: 'Facile', label: 'Facile' }, { value: 'Medio', label: 'Medio' }, { value: 'Difficile', label: 'Difficile' }] },
  { name: 'pdf_path', label: 'URL PDF', type: 'url', required: false },
  { name: 'audio_url', label: 'URL Audio', type: 'url', required: false },
];

const paymentFields = [
  { name: 'user_id', label: 'ID Utente', type: 'number', required: true },
  { name: 'description', label: 'Descrizione', type: 'text', required: true },
  { name: 'amount', label: 'Importo (€)', type: 'number', required: true, step: '0.01' },
  { name: 'payment_date', label: 'Data Pagamento', type: 'date', required: false },
  { name: 'status', label: 'Stato', type: 'select', required: true, options: [{ value: 'Pagato', label: 'Pagato' }, { value: 'In attesa', label: 'In attesa' }] },
];

function quickAction(type) {
  if (type === 'event') eventModal.value.show = true;
  else if (type === 'score') scoreModal.value.show = true;
  else if (type === 'payment') paymentModal.value.show = true;
}

async function saveEvent(formData) {
  try { await axios.post('/events', formData); eventModal.value.show = false; } catch { toastError('Errore nel salvataggio.'); }
}
async function saveScore(formData) {
  try { await axios.post('/repertoire', formData); scoreModal.value.show = false; } catch { toastError('Errore nel salvataggio.'); }
}
async function savePayment(formData) {
  try { await axios.post('/accounting', formData); paymentModal.value.show = false; } catch { toastError('Errore nel salvataggio.'); }
}

function buildCharts() {
  if (typeof Chart === 'undefined') return;

  // Payment donut
  if (paymentChartEl.value) {
    new Chart(paymentChartEl.value, {
      type: 'doughnut',
      data: {
        labels: ['Pagato', 'In attesa'],
        datasets: [{
          data: [stats.value.payments?.pagato ?? 0, stats.value.payments?.in_attesa ?? 0],
          backgroundColor: ['#C9A84C', '#333333'],
          borderColor: ['#e2c97e', '#444444'],
          borderWidth: 1,
        }],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
          tooltip: {
            callbacks: {
              label: (ctx) => ` ${ctx.label}: ${ctx.raw}`,
            },
          },
        },
        cutout: '65%',
      },
    });
  }

  // Attendance bar
  if (attendanceChartEl.value && stats.value.attendance?.length > 0) {
    const labels = stats.value.attendance.map(a => a.event);
    const data = stats.value.attendance.map(a => a.percentage);

    new Chart(attendanceChartEl.value, {
      type: 'bar',
      data: {
        labels,
        datasets: [{
          label: '% Presenti',
          data,
          backgroundColor: 'rgba(201, 168, 76, 0.7)',
          borderColor: '#C9A84C',
          borderWidth: 1,
          borderRadius: 4,
        }],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          y: {
            beginAtZero: true,
            max: 100,
            ticks: { color: '#9ca3af', callback: (v) => v + '%' },
            grid: { color: '#333333' },
          },
          x: {
            ticks: {
              color: '#9ca3af',
              maxRotation: 30,
              callback: function (val, idx) {
                const label = this.getLabelForValue(idx);
                return label.length > 15 ? label.substring(0, 15) + '…' : label;
              },
            },
            grid: { color: '#333333' },
          },
        },
        plugins: {
          legend: { display: false },
        },
      },
    });
  }
}

onMounted(async () => {
  // Load Chart.js via CDN dynamically.
  // NOTE: Add integrity="sha384-<hash>" to the script tag below and verify
  // against https://www.jsdelivr.com/package/npm/chart.js after deployment.
  await new Promise((resolve, reject) => {
    if (typeof Chart !== 'undefined') { resolve(); return; }
    const script = document.createElement('script');
    script.src = 'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js';
    script.crossOrigin = 'anonymous';
    script.onload = resolve;
    script.onerror = reject;
    document.head.appendChild(script);
  });

  try {
    const { data } = await axios.get('/dashboard/stats');
    stats.value = data;
  } catch {
    // Non-fatal; show empty charts
  } finally {
    loading.value = false;
  }

  await nextTick();
  buildCharts();
});
</script>

<style scoped>
</style>
