import { publicApi } from '../api';
import { showToast } from '../ui/toast';

export async function initCheckInPage() {
    const root = document.getElementById('check-in-root');
    if (!root) return;

    const token = root.dataset.qrToken;
    const form = document.getElementById('check-in-form');
    const meta = document.getElementById('check-in-meta');
    const expiredBox = document.getElementById('check-in-expired');
    const successBox = document.getElementById('check-in-success');

    try {
        const res = await publicApi.checkInMeta(token);
        if (res.data.session) {
            meta.textContent = `${res.data.session.title}${res.data.session.course_section_code ? ' · '+res.data.session.course_section_code : ''}`;
        }
        if (res.data.expired) {
            expiredBox.classList.remove('hidden');
            form?.classList.add('hidden');
        } else {
            form?.classList.remove('hidden');
        }
    } catch (error) {
        showToast(error.message, 'error');
    }

    form?.addEventListener('submit', async (event) => {
        event.preventDefault();
        const identifier = form.identifier.value.trim();
        try {
            const res = await publicApi.checkIn(token, identifier);
            successBox.textContent = res.message ?? 'Điểm danh thành công.';
            successBox.classList.remove('hidden');
            form.reset();
            showToast(res.message ?? 'Điểm danh thành công.');
        } catch (error) {
            showToast(error.message, 'error');
        }
    });
}
