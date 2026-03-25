<template>
  <Transition name="player-slide">
    <div
      v-if="audioUrl"
      class="fixed bottom-0 left-0 right-0 z-50 bg-dark-surface border-t border-dark-border shadow-2xl"
    >
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3">
        <div class="flex items-center gap-4">
          <!-- Track info -->
          <div class="flex-1 min-w-0">
            <p class="text-xs text-gray-400 uppercase tracking-wider mb-0.5">In riproduzione</p>
            <p class="text-white font-medium text-sm truncate">{{ trackName || 'Traccia audio' }}</p>
          </div>

          <!-- Audio controls -->
          <div class="flex-shrink-0">
            <audio
              ref="audioEl"
              :src="audioUrl"
              controls
              class="h-8"
              style="filter: invert(0.8) sepia(1) saturate(2) hue-rotate(5deg);"
              @ended="onEnded"
            ></audio>
          </div>

          <!-- Close button -->
          <button
            @click="close"
            class="flex-shrink-0 w-8 h-8 flex items-center justify-center rounded-full bg-dark-card border border-dark-border text-gray-400 hover:text-white hover:border-gold transition-colors"
            title="Chiudi player"
            aria-label="Chiudi player audio"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>
      </div>
    </div>
  </Transition>
</template>

<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
  audioUrl: {
    type: String,
    default: '',
  },
  trackName: {
    type: String,
    default: '',
  },
});

const emit = defineEmits(['close']);
const audioEl = ref(null);

// Auto-play when a new URL is set
watch(() => props.audioUrl, (newUrl) => {
  if (newUrl && audioEl.value) {
    audioEl.value.load();
    audioEl.value.play().catch(() => {
      // Autoplay blocked by browser policy; user must press play manually
    });
  }
});

function close() {
  if (audioEl.value) {
    audioEl.value.pause();
    audioEl.value.currentTime = 0;
  }
  emit('close');
}

function onEnded() {
  // Track finished naturally
}
</script>

<style scoped>
.player-slide-enter-active,
.player-slide-leave-active {
  transition: transform 0.3s ease, opacity 0.3s ease;
}
.player-slide-enter-from,
.player-slide-leave-to {
  transform: translateY(100%);
  opacity: 0;
}
</style>
