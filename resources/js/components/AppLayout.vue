<template>
  <div class="min-h-screen bg-dark flex flex-col">
    <!-- Top Navigation -->
    <nav class="bg-dark-surface border-b border-dark-border sticky top-0 z-40">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
          <!-- Logo -->
          <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg bg-dark-card border border-dark-border flex items-center justify-center">
              <svg class="w-5 h-5 text-gold" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path d="M9 3v10.55A4 4 0 1 0 11 17V7h4V3H9zm-2 16a2 2 0 1 1 0-4 2 2 0 0 1 0 4z"/>
              </svg>
            </div>
            <span class="text-gold font-bold text-lg tracking-wide">GalliSay</span>
          </div>

          <!-- Desktop Nav Links -->
          <div class="hidden md:flex items-center gap-1">
            <RouterLink
              v-for="link in navLinks"
              :key="link.to"
              :to="link.to"
              class="px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200"
              :class="$route.path === link.to
                ? 'bg-dark-card text-gold border border-dark-border'
                : 'text-gray-400 hover:text-white hover:bg-dark-card'"
            >
              {{ link.label }}
            </RouterLink>
          </div>

          <!-- User menu -->
          <div class="flex items-center gap-3">
            <span v-if="user" class="hidden sm:block text-xs text-gray-400">
              {{ user.first_name }} {{ user.last_name }}
              <span class="ml-1 text-gold">({{ user.role }})</span>
            </span>
            <button
              @click="logout"
              class="btn-ghost text-xs py-1.5 px-3"
              title="Esci"
            >
              Esci
            </button>
          </div>
        </div>
      </div>

      <!-- Mobile Nav -->
      <div class="md:hidden border-t border-dark-border">
        <div class="flex overflow-x-auto px-4 py-2 gap-1">
          <RouterLink
            v-for="link in navLinks"
            :key="link.to"
            :to="link.to"
            class="flex-shrink-0 px-3 py-1.5 rounded-lg text-sm font-medium transition-colors duration-200 whitespace-nowrap"
            :class="$route.path === link.to
              ? 'bg-dark-card text-gold border border-dark-border'
              : 'text-gray-400 hover:text-white'"
          >
            {{ link.label }}
          </RouterLink>
        </div>
      </div>
    </nav>

    <!-- Page Content -->
    <main class="flex-1 max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-8">
      <slot />
    </main>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { useRouter } from 'vue-router';
import { inject } from 'vue';

const axios = inject('axios');
const router = useRouter();

const user = computed(() => {
  try {
    return JSON.parse(localStorage.getItem('auth_user') || 'null');
  } catch {
    return null;
  }
});

const navLinks = computed(() => {
  const links = [
    { to: '/repertoire', label: 'Repertorio' },
    { to: '/events', label: 'Eventi' },
    { to: '/accounting', label: 'Contabilità' },
  ];
  if (user.value && ['Admin', 'Direttore'].includes(user.value.role)) {
    links.push({ to: '/dashboard', label: 'Dashboard' });
  }
  return links;
});

async function logout() {
  try {
    await axios.post('/auth/logout');
  } catch {
    // Proceed with local logout even if request fails
  } finally {
    localStorage.removeItem('auth_token');
    localStorage.removeItem('auth_user');
    router.push('/login');
  }
}
</script>
