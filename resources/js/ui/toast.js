export function showToast(message, type = 'success') {
    const existing = document.getElementById('cca-toast');
    if (existing) {
        existing.remove();
    }

    const el = document.createElement('div');
    el.id = 'cca-toast';
    el.setAttribute('role', 'alert');
    el.className = [
        'fixed right-4 top-4 z-50 max-w-sm rounded-lg px-4 py-3 text-sm text-white shadow-lg',
        type === 'error' ? 'bg-red-600' : 'bg-green-600',
    ].join(' ');
    el.textContent = message;
    document.body.appendChild(el);
    setTimeout(() => el.remove(), 3200);
}

export function fieldErrors(payload) {
    const errors = payload?.errors ?? {};
    return Object.values(errors).flat().join(' ');
}
