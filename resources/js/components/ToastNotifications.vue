<template>
  <Teleport to="body">
    <div class="fixed top-4 right-4 z-[9999] flex flex-col gap-2 pointer-events-none" aria-live="polite">
      <TransitionGroup name="toast">
        <div
          v-for="toast in toasts"
          :key="toast.id"
          class="pointer-events-auto flex items-start gap-3 px-4 py-3 rounded-xl border shadow-xl text-sm font-medium max-w-sm w-full"
          :class="{
            'bg-dark-card border-green-700 text-green-300': toast.type === 'success',
            'bg-dark-card border-red-700 text-red-300': toast.type === 'error',
            'bg-dark-card border-dark-border text-gray-200': toast.type === 'info',
          }"
        >
          <!-- Icon -->
          <span v-if="toast.type === 'success'" class="mt-0.5 flex-shrink-0">✓</span>
          <span v-else-if="toast.type === 'error'" class="mt-0.5 flex-shrink-0">✕</span>
          <span v-else class="mt-0.5 flex-shrink-0">ℹ</span>

          <span class="flex-1">{{ toast.message }}</span>
        </div>
      </TransitionGroup>
    </div>
  </Teleport>
</template>

<script setup>
import { useToast } from '../composables/useToast.js';

const { toasts } = useToast();
</script>

<style scoped>
.toast-enter-active,
.toast-leave-active {
  transition: all 0.3s ease;
}
.toast-enter-from {
  opacity: 0;
  transform: translateX(100%);
}
.toast-leave-to {
  opacity: 0;
  transform: translateX(100%);
}
.toast-move {
  transition: transform 0.3s ease;
}
</style>
