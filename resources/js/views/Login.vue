<template>
  <div class="min-h-screen bg-dark flex items-center justify-center px-4">
    <div class="w-full max-w-md">
      <!-- Logo / Wordmark -->
      <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-dark-card border border-dark-border mb-4">
          <!-- Musical note SVG -->
          <svg class="w-8 h-8 text-gold" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path d="M9 3v10.55A4 4 0 1 0 11 17V7h4V3H9zm-2 16a2 2 0 1 1 0-4 2 2 0 0 1 0 4z"/>
          </svg>
        </div>
        <h1 class="text-3xl font-bold text-gold tracking-wide">GalliSay</h1>
        <p class="text-gray-400 mt-1 text-sm">Gestione Coro</p>
      </div>

      <!-- Login Card -->
      <div class="card">
        <h2 class="text-xl font-semibold text-white mb-6">Accedi al tuo account</h2>

        <form @submit.prevent="handleLogin" novalidate>
          <!-- Email -->
          <div class="mb-4">
            <label for="email" class="block text-sm font-medium text-gray-300 mb-1">Email</label>
            <input
              id="email"
              v-model="form.email"
              type="email"
              autocomplete="email"
              class="input-dark"
              placeholder="nome@esempio.it"
              required
            />
          </div>

          <!-- Password -->
          <div class="mb-6">
            <label for="password" class="block text-sm font-medium text-gray-300 mb-1">Password</label>
            <input
              id="password"
              v-model="form.password"
              type="password"
              autocomplete="current-password"
              class="input-dark"
              placeholder="••••••••"
              required
            />
          </div>

          <!-- Error message -->
          <div v-if="errorMessage" class="mb-4 p-3 rounded-lg bg-red-900/40 border border-red-700 text-red-300 text-sm">
            {{ errorMessage }}
          </div>

          <!-- Submit -->
          <button
            type="submit"
            :disabled="loading"
            class="btn-gold w-full flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <svg v-if="loading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24" aria-hidden="true">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
            </svg>
            {{ loading ? 'Accesso in corso…' : 'Accedi' }}
          </button>
        </form>
      </div>

      <p class="text-center text-gray-600 text-xs mt-6">GalliSay &copy; {{ new Date().getFullYear() }}</p>
    </div>
  </div>
</template>

<script setup>
import { ref, inject } from 'vue';
import { useRouter } from 'vue-router';

const axios = inject('axios');
const router = useRouter();

const form = ref({ email: '', password: '' });
const loading = ref(false);
const errorMessage = ref('');

async function handleLogin() {
  errorMessage.value = '';

  if (!form.value.email || !form.value.password) {
    errorMessage.value = 'Inserisci email e password.';
    return;
  }

  loading.value = true;
  try {
    const { data } = await axios.post('/auth/login', {
      email: form.value.email,
      password: form.value.password,
    });

    localStorage.setItem('auth_token', data.token);
    localStorage.setItem('auth_user', JSON.stringify(data.user));

    router.push('/repertoire');
  } catch (err) {
    const msg = err.response?.data?.message
      || err.response?.data?.errors?.email?.[0]
      || 'Errore durante il login. Riprova.';
    errorMessage.value = msg;
  } finally {
    loading.value = false;
  }
}
</script>

<style scoped>
/* Component-specific overrides if needed */
</style>
