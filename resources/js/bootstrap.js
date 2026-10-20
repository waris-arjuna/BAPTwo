import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Global error helper
window.showAppError = function (title, message, errors = []) {
    let detailMessage = message || 'Permintaan tidak dapat diproses.';
    if (Array.isArray(errors) && errors.length > 0) {
        detailMessage += ' ' + errors.join(' ');
    }
    if (typeof window.toast?.error === 'function') {
        window.toast.error(title || 'Terjadi Kesalahan', detailMessage);
    } else {
        window.dispatchEvent(new CustomEvent('toast-notify', {
            detail: {
                type: 'error',
                title: title || 'Terjadi Kesalahan',
                message: detailMessage,
            }
        }));
    }
};

// Global Axios Response Interceptor (Poin 19: Tangani 4xx/5xx, Timeout, Sesi Habis, Jaringan)
window.axios.interceptors.response.use(
    response => response,
    error => {
        let title = 'Terjadi Kesalahan Sistem';
        let message = 'Permintaan tidak dapat diproses. Silakan coba beberapa saat lagi.';
        let errors = [];

        if (error.response) {
            const status = error.response.status;
            if (status === 419) {
                title = 'Sesi Telah Berakhir';
                message = 'Sesi Anda telah kedaluwarsa atau token keamanan tidak valid. Silakan muat ulang halaman atau login kembali.';
            } else if (status === 422) {
                title = 'Kesalahan Validasi Data';
                message = error.response.data.message || 'Data yang dikirimkan tidak memenuhi persyaratan validasi.';
                if (error.response.data.errors) {
                    errors = Object.values(error.response.data.errors).flat();
                }
            } else if (status === 403) {
                title = 'Akses Ditolak';
                message = error.response.data.message || 'Anda tidak memiliki hak akses untuk melakukan aksi ini.';
            } else if (status === 404) {
                title = 'Data Tidak Ditemukan';
                message = 'Data atau alamat yang dituju tidak ditemukan di server.';
            } else if (status >= 500) {
                title = 'Kesalahan Server Internal';
                message = 'Terjadi kendala pada server saat memproses data. Silakan hubungi Administrator.';
            }
        } else if (error.code === 'ECONNABORTED' || (error.message && error.message.includes('timeout'))) {
            title = 'Batas Waktu Jaringan Habis';
            message = 'Koneksi ke server mengalami timeout. Periksa koneksi internet Anda dan coba lagi.';
        } else if (error.request) {
            title = 'Gangguan Koneksi Jaringan';
            message = 'Tidak dapat terhubung ke server. Pastikan perangkat Anda terhubung ke jaringan internet/lokal.';
        }

        window.showAppError(title, message, errors);
        return Promise.reject(error);
    }
);
