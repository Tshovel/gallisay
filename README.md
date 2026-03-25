# GalliSay 🎵

**GalliSay** is a choir management Progressive Web App (PWA) built with Laravel 10 and Vue 3. It helps choir administrators, directors, and members manage repertoire, events, attendance, and accounting — all from a beautiful dark-themed mobile-first interface.

---

## Features

| Module | Description |
|---|---|
| 🔐 **Authentication** | JWT-like token auth via Laravel Sanctum |
| 🎼 **Repertoire** | Manage sheet music with PDF preview, audio playback, and share |
| 📅 **Events** | Schedule rehearsals & concerts; members mark their attendance |
| 💰 **Accounting** | Track member payments with status badges |
| 📊 **Dashboard** | Donut & bar charts for payment status and rehearsal attendance |
| 📱 **PWA** | Installable, offline-capable via Service Worker |

---

## Tech Stack

| Layer | Technology |
|---|---|
| Backend | PHP 8.1 / Laravel 10 (REST API) |
| Frontend | Vue 3 (Composition API, `<script setup>`) |
| Styling | Tailwind CSS 3 — custom "Aureo" dark theme |
| Build | Vite 4 + `laravel-vite-plugin` |
| Auth | Laravel Sanctum (Bearer tokens) |
| Database | MySQL 8 |
| PWA | Web App Manifest + Service Worker |
| Charts | Chart.js 4 (CDN, dynamic import) |

---

## Installation

### Prerequisites
- PHP 8.1+
- Composer
- Node.js 18+ & npm
- MySQL 8

### Steps

```bash
# 1. Clone the repository
git clone https://github.com/gallisay/gallisay.git
cd gallisay

# 2. Install PHP dependencies
composer install

# 3. Install JS dependencies
npm install

# 4. Configure environment
cp .env.example .env
php artisan key:generate

# 5. Set up the database
# Edit DB_* values in .env, then:
php artisan migrate

# 6. Build frontend assets
npm run build

# 7. Serve
php artisan serve
```

Visit [http://localhost:8000](http://localhost:8000).

### Development (hot reload)

```bash
# Terminal 1 – Laravel API server
php artisan serve

# Terminal 2 – Vite dev server
npm run dev
```

---

## API Documentation

All API routes are prefixed with `/api/v1`. Protected routes require the `Authorization: Bearer <token>` header.

### Authentication

| Method | Endpoint | Auth | Description |
|---|---|---|---|
| `POST` | `/api/v1/auth/login` | ❌ | Login, returns token |
| `POST` | `/api/v1/auth/logout` | ✅ | Revoke current token |
| `GET` | `/api/v1/auth/me` | ✅ | Get current user |

### Repertoire

| Method | Endpoint | Roles | Description |
|---|---|---|---|
| `GET` | `/api/v1/repertoire` | All | List scores (paginated, filterable) |
| `POST` | `/api/v1/repertoire` | Admin, Direttore | Create score |
| `PUT` | `/api/v1/repertoire/{id}` | Admin, Direttore | Update score |
| `DELETE` | `/api/v1/repertoire/{id}` | Admin, Direttore | Delete score |

### Events

| Method | Endpoint | Roles | Description |
|---|---|---|---|
| `GET` | `/api/v1/events` | All | List events (paginated) |
| `POST` | `/api/v1/events` | Admin, Direttore | Create event |
| `PUT` | `/api/v1/events/{id}` | Admin, Direttore | Update event |
| `DELETE` | `/api/v1/events/{id}` | Admin, Direttore | Delete event |
| `POST` | `/api/v1/events/{id}/attendance` | All | Mark attendance |

### Accounting

| Method | Endpoint | Roles | Description |
|---|---|---|---|
| `GET` | `/api/v1/accounting` | All | List payments (paginated) |
| `POST` | `/api/v1/accounting` | Admin | Create payment |
| `PUT` | `/api/v1/accounting/{id}` | Admin | Update payment |

### Dashboard

| Method | Endpoint | Roles | Description |
|---|---|---|---|
| `GET` | `/api/v1/dashboard/stats` | All | Payment & attendance stats |

---

## User Roles

| Role | Permissions |
|---|---|
| **Admin** | Full access: all CRUD + accounting management |
| **Direttore** | Manage repertoire & events; view accounting |
| **Corista** | View repertoire & events; mark own attendance; view own payments |

### Vocal Sections
`S` (Soprano) · `A` (Alto) · `T` (Tenore) · `B` (Basso)

---

## PWA Installation

1. Open GalliSay in Chrome/Edge/Safari on any device.
2. Look for the **"Install App"** prompt in the browser address bar.
3. Tap **Install** — GalliSay will be added to your home screen.
4. The app works offline thanks to a network-first Service Worker cache strategy for API calls and cache-first for static assets.

---

## Design — "Aureo" Dark Theme

| Token | Value | Usage |
|---|---|---|
| `--bg-dark` | `#0f0f0f` | Page background |
| `--bg-surface` | `#1a1a1a` | Nav, sidebar |
| `--bg-card` | `#222222` | Cards, modals |
| `--border` | `#333333` | Borders, dividers |
| `--gold` | `#C9A84C` | Primary accent |
| `--gold-light` | `#e2c97e` | Hover accent |
| Font | Inter | All text |

---

## Screenshots

> _Screenshots will be added after initial deployment._

| Login | Repertoire | Events | Dashboard |
|---|---|---|---|
| ![Login](docs/screenshots/login.png) | ![Repertoire](docs/screenshots/repertoire.png) | ![Events](docs/screenshots/events.png) | ![Dashboard](docs/screenshots/dashboard.png) |

---

## License

MIT © GalliSay
