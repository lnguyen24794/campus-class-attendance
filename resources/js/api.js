/**
 * Thin AJAX client for /api/v1 with CSRF + JSON handling.
 */
function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
}

export class ApiError extends Error {
    constructor(message, status, payload = null) {
        super(message);
        this.name = 'ApiError';
        this.status = status;
        this.payload = payload;
    }
}

export async function api(path, options = {}) {
    const headers = {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': csrfToken(),
        ...(options.body ? { 'Content-Type': 'application/json' } : {}),
        ...(options.headers ?? {}),
    };

    const response = await fetch(`/api/v1${path}`, {
        credentials: 'same-origin',
        ...options,
        headers,
        body: options.body ? JSON.stringify(options.body) : undefined,
    });

    const payload = await response.json().catch(() => null);

    if (!response.ok || payload?.success === false) {
        const message = payload?.message
            ?? (payload?.errors ? Object.values(payload.errors).flat().join(' ') : null)
            ?? `HTTP ${response.status}`;
        throw new ApiError(message, response.status, payload);
    }

    return payload;
}

export const adminApi = {
    dashboard: () => api('/admin/dashboard'),
    list: (resource, params = {}) => {
        const query = new URLSearchParams(
            Object.entries(params).filter(([, v]) => v !== '' && v != null),
        ).toString();
        return api(`/admin/${resource}${query ? `?${query}` : ''}`);
    },
    get: (resource, id) => api(`/admin/${resource}/${id}`),
    create: (resource, body) => api(`/admin/${resource}`, { method: 'POST', body }),
    update: (resource, id, body) => api(`/admin/${resource}/${id}`, { method: 'PUT', body }),
    destroy: (resource, id) => api(`/admin/${resource}/${id}`, { method: 'DELETE' }),
    sessionOptions: () => api('/admin/attendance-sessions/options'),
    listSessions: (params = {}) => {
        const query = new URLSearchParams(
            Object.entries(params).filter(([, v]) => v !== '' && v != null),
        ).toString();
        return api(`/admin/attendance-sessions${query ? `?${query}` : ''}`);
    },
    createSession: (body) => api('/admin/attendance-sessions', { method: 'POST', body }),
    liveSession: (id) => api(`/admin/attendance-sessions/${id}/live`),
    refreshQr: (id) => api(`/admin/attendance-sessions/${id}/refresh-qr`, { method: 'POST' }),
};

export const publicApi = {
    checkInMeta: (token) => api(`/public/check-in/${token}`),
    checkIn: (token, identifier) => api(`/public/check-in/${token}`, {
        method: 'POST',
        body: { identifier },
    }),
};
