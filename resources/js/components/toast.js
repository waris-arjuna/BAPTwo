const TOAST_DURATIONS = {
    success: 3000,
    info: 3000,
    warning: 3000,
    error: 3000,
};

const MAX_TOASTS = 4;
const DEDUP_INTERVAL_MS = 1500;

export default function toastManager(initialToasts = []) {
    return {
        toasts: [],
        recentToasts: [],

        init() {
            if (Array.isArray(initialToasts)) {
                initialToasts.forEach((t) => this.addToast(t));
            }

            window.addEventListener('toast-notify', (event) => {
                if (event.detail) {
                    this.addToast(event.detail);
                }
            });
        },

        addToast(payload) {
            if (!payload) return;

            const type = ['success', 'error', 'warning', 'info'].includes(payload.type)
                ? payload.type
                : 'info';

            const title = payload.title || this.getDefaultTitle(type);
            const message = payload.message || (typeof payload === 'string' ? payload : '');
            const duration = Number(payload.duration) > 0
                ? Number(payload.duration)
                : (TOAST_DURATIONS[type] || 3000);

            const now = Date.now();
            const signature = `${type}:${title}:${message}`;
            const isDuplicate = this.recentToasts.some(
                (item) => item.sig === signature && now - item.time < DEDUP_INTERVAL_MS
            );

            if (isDuplicate) return;

            this.recentToasts = this.recentToasts.filter((item) => now - item.time < DEDUP_INTERVAL_MS);
            this.recentToasts.push({ sig: signature, time: now });

            const toast = {
                id: 'toast-' + now + '-' + Math.random().toString(36).slice(2, 7),
                type,
                title,
                message,
                duration,
                isTouchPaused: false,
            };

            // Batasi maksimal 4 toast terlihat bersamaan, hapus yang tertua
            if (this.toasts.length >= MAX_TOASTS) {
                this.toasts.pop();
            }

            // Toast terbaru berada di posisi paling atas
            this.toasts.unshift(toast);
        },

        removeToast(id) {
            this.toasts = this.toasts.filter((t) => t.id !== id);
        },

        toggleTouchPause(id) {
            const toast = this.toasts.find((t) => t.id === id);
            if (toast) {
                toast.isTouchPaused = !toast.isTouchPaused;
            }
        },

        getDefaultTitle(type) {
            const map = {
                success: 'Berhasil',
                error: 'Gagal',
                warning: 'Perhatian',
                info: 'Informasi',
            };
            return map[type] || 'Notifikasi';
        },
    };
}

// Global helper window.toast
window.toast = function (options) {
    if (typeof options === 'string') {
        options = { message: options };
    }
    window.dispatchEvent(new CustomEvent('toast-notify', { detail: options }));
};

window.toast.success = (title, message, duration) =>
    window.toast({ type: 'success', title: message ? title : 'Berhasil', message: message || title, duration });

window.toast.error = (title, message, duration) =>
    window.toast({ type: 'error', title: message ? title : 'Gagal', message: message || title, duration });

window.toast.warning = (title, message, duration) =>
    window.toast({ type: 'warning', title: message ? title : 'Perhatian', message: message || title, duration });

window.toast.info = (title, message, duration) =>
    window.toast({ type: 'info', title: message ? title : 'Informasi', message: message || title, duration });
