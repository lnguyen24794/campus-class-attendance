import { adminApi } from '../api';
import { iconEye } from '../ui/icons';
import { showToast } from '../ui/toast';

export async function initDashboardPage() {
    const root = document.getElementById('dashboard-root');
    if (!root) return;

    try {
        const res = await adminApi.dashboard();
        const { counts, open_sessions: sessions } = res.data;

        root.querySelector('[data-count="students"]').textContent = counts.students;
        root.querySelector('[data-count="lecturers"]').textContent = counts.lecturers;
        root.querySelector('[data-count="subjects"]').textContent = counts.subjects;
        root.querySelector('[data-count="periods"]').textContent = counts.periods;

        const tbody = root.querySelector('#open-sessions-tbody');
        if (!sessions.length) {
            tbody.innerHTML = `<tr><td colspan="4" class="px-4 py-8 text-center text-gray-500">Chưa có buổi nào đang mở.</td></tr>`;
            return;
        }

        tbody.innerHTML = sessions.map((s) => `
            <tr>
                <td>${s.title}</td>
                <td>${s.course_section_code ?? '—'}</td>
                <td>${s.period_name ?? '—'}</td>
                <td class="text-right">
                    <a href="${s.live_url}" class="ht-action-btn">${iconEye}<span>Xem Live</span></a>
                </td>
            </tr>
        `).join('');
    } catch (error) {
        showToast(error.message, 'error');
    }
}
