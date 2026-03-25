import { createApp, defineComponent, h } from 'vue';
import { createRouter, createWebHistory } from 'vue-router';
import axios from 'axios';

import '../css/app.css';

// Views
import Login from './views/Login.vue';
import Repertoire from './views/Repertoire.vue';
import Events from './views/Events.vue';
import Accounting from './views/Accounting.vue';
import Dashboard from './views/Dashboard.vue';

// Toast notifications
import ToastNotifications from './components/ToastNotifications.vue';

// Configure Axios
axios.defaults.baseURL = '/api/v1';

// Add Bearer token from localStorage to every request
axios.interceptors.request.use((config) => {
    const token = localStorage.getItem('auth_token');
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
});

// Redirect to login on 401 responses
axios.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response?.status === 401) {
            localStorage.removeItem('auth_token');
            localStorage.removeItem('auth_user');
            router.push('/login');
        }
        return Promise.reject(error);
    }
);

// Routes
const routes = [
    { path: '/', redirect: '/repertoire' },
    { path: '/login', component: Login, meta: { public: true } },
    { path: '/repertoire', component: Repertoire, meta: { requiresAuth: true } },
    { path: '/events', component: Events, meta: { requiresAuth: true } },
    { path: '/accounting', component: Accounting, meta: { requiresAuth: true } },
    { path: '/dashboard', component: Dashboard, meta: { requiresAuth: true } },
    { path: '/:pathMatch(.*)*', redirect: '/repertoire' },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

// Navigation guard
router.beforeEach((to, from, next) => {
    const token = localStorage.getItem('auth_token');
    if (to.meta.requiresAuth && !token) {
        next('/login');
    } else if (to.path === '/login' && token) {
        next('/repertoire');
    } else {
        next();
    }
});

// Root app component: router-view + global toast notifications
const RootApp = defineComponent({
    components: { ToastNotifications },
    template: '<router-view /><ToastNotifications />',
});

// Mount app
const app = createApp(RootApp);

app.use(router);
app.config.globalProperties.$axios = axios;
app.provide('axios', axios);
app.mount('#app');

// Register Service Worker
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/service-worker.js')
            .then((reg) => {
                if (import.meta.env.DEV) {
                    console.log('SW registered:', reg.scope);
                }
            })
            .catch((err) => {
                if (import.meta.env.DEV) {
                    console.warn('SW registration failed:', err);
                }
            });
    });
}
