<template>
  <Teleport to="body">
    <Transition name="modal-fade">
      <div
        v-if="show"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm"
        role="dialog"
        :aria-label="title"
        @click.self="$emit('close')"
      >
        <div class="bg-dark-card border border-dark-border rounded-xl w-full max-w-lg shadow-2xl">
          <!-- Header -->
          <div class="flex items-center justify-between px-6 py-4 border-b border-dark-border">
            <h3 class="text-white font-semibold text-lg">{{ title }}</h3>
            <button
              @click="$emit('close')"
              class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-dark-surface text-gray-400 hover:text-white transition-colors"
              aria-label="Chiudi"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>

          <!-- Form Body -->
          <form @submit.prevent="handleSave" class="px-6 py-5 space-y-4 max-h-[65vh] overflow-y-auto">
            <div v-for="field in fields" :key="field.name">
              <label :for="`field-${field.name}`" class="block text-sm font-medium text-gray-300 mb-1">
                {{ field.label }}
                <span v-if="field.required" class="text-gold ml-0.5">*</span>
              </label>

              <!-- Textarea -->
              <textarea
                v-if="field.type === 'textarea'"
                :id="`field-${field.name}`"
                v-model="formData[field.name]"
                :required="field.required"
                rows="3"
                class="input-dark resize-none"
                :placeholder="field.placeholder || ''"
              ></textarea>

              <!-- Select -->
              <select
                v-else-if="field.type === 'select'"
                :id="`field-${field.name}`"
                v-model="formData[field.name]"
                :required="field.required"
                class="input-dark"
              >
                <option v-if="!field.required" value="">— Seleziona —</option>
                <option
                  v-for="opt in field.options"
                  :key="opt.value"
                  :value="opt.value"
                >
                  {{ opt.label }}
                </option>
              </select>

              <!-- Other inputs (text, email, number, date, time, url, etc.) -->
              <input
                v-else
                :id="`field-${field.name}`"
                v-model="formData[field.name]"
                :type="field.type"
                :required="field.required"
                :step="field.step || undefined"
                :placeholder="field.placeholder || ''"
                class="input-dark"
              />
            </div>

            <!-- Form error -->
            <div v-if="formError" class="p-3 rounded-lg bg-red-900/40 border border-red-700 text-red-300 text-sm">
              {{ formError }}
            </div>
          </form>

          <!-- Footer Actions -->
          <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-dark-border">
            <button type="button" @click="$emit('close')" class="btn-ghost">
              Annulla
            </button>
            <button type="button" @click="handleSave" class="btn-gold">
              Salva
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
  show: {
    type: Boolean,
    default: false,
  },
  title: {
    type: String,
    default: 'Form',
  },
  fields: {
    type: Array,
    default: () => [],
  },
  initialData: {
    type: Object,
    default: null,
  },
});

const emit = defineEmits(['save', 'close']);

const formData = ref({});
const formError = ref('');

// Reset form when modal opens or initialData changes
watch(
  () => [props.show, props.initialData],
  ([show, data]) => {
    if (show) {
      formError.value = '';
      if (data) {
        formData.value = { ...data };
      } else {
        // Initialize empty values based on fields
        const empty = {};
        props.fields.forEach((f) => { empty[f.name] = ''; });
        formData.value = empty;
      }
    }
  },
  { immediate: true }
);

function handleSave() {
  formError.value = '';

  // Basic required validation
  for (const field of props.fields) {
    if (field.required && !formData.value[field.name] && formData.value[field.name] !== 0) {
      formError.value = `Il campo "${field.label}" è obbligatorio.`;
      return;
    }
  }

  emit('save', { ...formData.value });
}
</script>

<style scoped>
.modal-fade-enter-active,
.modal-fade-leave-active {
  transition: opacity 0.2s ease;
}
.modal-fade-enter-from,
.modal-fade-leave-to {
  opacity: 0;
}
.modal-fade-enter-active .bg-dark-card,
.modal-fade-leave-active .bg-dark-card {
  transition: transform 0.2s ease;
}
.modal-fade-enter-from .bg-dark-card {
  transform: scale(0.95) translateY(-8px);
}
</style>
