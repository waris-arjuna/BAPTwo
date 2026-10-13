export default function tableFilter(targetSelector = '#table-body-partial') {
    return {
        loading: false,
        target: targetSelector,
        hasFilter: false,

        init() {
            this.updateHasFilter();
            window.addEventListener('popstate', () => {
                this.fetchPartial(window.location.href, false);
            });
        },

        updateHasFilter() {
            const form = this.$el;
            if (!form) return;
            const formData = new FormData(form);
            this.hasFilter = Array.from(formData.values()).some(
                val => typeof val === 'string' && val.trim() !== ''
            );
        },

        doFilter(form) {
            const formData = new FormData(form);
            const params = new URLSearchParams();
            for (const [key, value] of formData.entries()) {
                if (typeof value === 'string' && value.trim() !== '') {
                    params.append(key, value.trim());
                }
            }
            this.hasFilter = params.toString().length > 0;
            const queryString = params.toString();
            const baseUrl = form.getAttribute('action') || window.location.pathname;
            const url = queryString ? `${baseUrl}?${queryString}` : baseUrl;
            this.fetchPartial(url, true);
        },

        resetFilter(form) {
            if (!form) form = this.$el;
            const inputs = form.querySelectorAll('input, select');
            inputs.forEach(el => {
                if (el.type !== 'submit' && el.type !== 'button') {
                    el.value = '';
                    if (el._flatpickr) {
                        el._flatpickr.clear();
                    }
                }
            });
            this.hasFilter = false;
            const baseUrl = form.getAttribute('action') || window.location.pathname;
            this.fetchPartial(baseUrl, true);
        },

        async fetchPartial(url, push = true) {
            this.loading = true;
            try {
                const response = await fetch(url, {
                    headers: {
                        'X-Filter-Partial': '1',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (!response.ok) {
                    window.location.href = url;
                    return;
                }

                const html = await response.text();
                const container = document.querySelector(this.target);
                if (container) {
                    container.innerHTML = html;
                }

                if (push && window.location.href !== url) {
                    window.history.pushState({ path: url }, '', url);
                }
            } catch (err) {
                console.error('Filter fetch error:', err);
                window.location.href = url;
            } finally {
                this.loading = false;
            }
        },
    };
}
