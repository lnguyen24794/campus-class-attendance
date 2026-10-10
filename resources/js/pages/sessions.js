import { adminApi } from '../api';
import { showToast } from '../ui/toast';

export async function initSessionCreatePage() {
    const form = document.getElementById('session-create-form');
    if (!form) return;

    const sectionSelect = form.querySelector('#course_section_id');
    const periodSelect = form.querySelector('#period_id');
    const durationInput = form.querySelector('#duration_minutes');
    const durationHint = document.getElementById('duration_hint');

    /** @type {Map<string, { duration_minutes: number, starts_at: string, ends_at: string, label: string }>} */
    const periodsById = new Map();
    let defaultMinutes = 45;

    function applyDurationFromPeriod() {
        if (!durationInput) return;
        const periodId = periodSelect?.value ?? '';
        const period = periodId ? periodsById.get(periodId) : null;

        if (period) {
            durationInput.value = String(period.duration_minutes);
            if (durationHint) {
                durationHint.textContent =
                    `Theo tiết đã chọn: ${period.starts_at}–${period.ends_at} → ${period.duration_minutes} phút. Giờ kết thúc buổi lấy theo tiết.`;
            }
            return;
        }

        durationInput.value = String(defaultMinutes);
        if (durationHint) {
            durationHint.textContent =
                `Chưa chọn tiết — mặc định ${defaultMinutes} phút/tiết. Chọn tiết để điền theo giờ bắt đầu–kết thúc.`;
        }
    }

    try {
        const res = await adminApi.sessionOptions();
        defaultMinutes = Number(res.data.minutes_per_period ?? 45);

        sectionSelect.innerHTML = `<option value="">— Chọn lớp —</option>` + res.data.sections
            .map((s) => `<option value="${s.id}">${s.label}</option>`).join('');

        periodsById.clear();
        (res.data.periods ?? []).forEach((p) => {
            periodsById.set(String(p.id), p);
        });

        periodSelect.innerHTML = `<option value="">— Không chọn —</option>` + res.data.periods
            .map((p) => `<option value="${p.id}">${p.label}</option>`).join('');

        applyDurationFromPeriod();
    } catch (error) {
        showToast(error.message, 'error');
    }

    periodSelect?.addEventListener('change', applyDurationFromPeriod);

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        const body = Object.fromEntries(new FormData(form).entries());
        if (!body.period_id) delete body.period_id;
        body.duration_minutes = Number(body.duration_minutes || defaultMinutes);
        body.late_after_minutes = Number(body.late_after_minutes ?? 15);

        try {
            const res = await adminApi.createSession(body);
            showToast('Đã mở buổi điểm danh.');
            window.location.href = res.data.live_url;
        } catch (error) {
            showToast(error.message, 'error');
        }
    });
}

export async function initSessionLivePage() {
    const root = document.getElementById('session-live-root');
    if (!root) return;

    const sessionId = root.dataset.sessionId;
    const qrBox = document.getElementById('qr-box');
    const checkInUrlEl = document.getElementById('check-in-url');
    const titleEl = document.getElementById('session-title');
    const metaEl = document.getElementById('session-meta');
    const tbody = document.getElementById('attendance-rows');
    const countEl = document.getElementById('attendance-count');
    const refreshBtn = document.getElementById('refresh-qr-btn');
    const copyBtn = document.getElementById('copy-check-in-url');
    let lastCheckInUrl = '';

    async function renderLive() {
        const res = await adminApi.liveSession(sessionId);
        const data = res.data;
        const lateMins = data.session.late_after_minutes ?? 15;
        titleEl.textContent = data.session.title;
        metaEl.textContent = [
            data.session.course_section_code,
            data.session.period_name ?? 'Không gắn tiết',
            `Kết thúc: ${data.session.closes_at ? new Date(data.session.closes_at).toLocaleTimeString('vi-VN') : '—'}`,
            `Đúng giờ ≤ ${lateMins} phút`,
        ].join(' · ');
        lastCheckInUrl = data.check_in_url;
        checkInUrlEl.textContent = data.check_in_url;
        qrBox.innerHTML = data.qr_svg;
        countEl.textContent = String(data.attendances.length);
        tbody.innerHTML = data.attendances.map(rowHtml).join('');
    }

    function statusLabelVi(row) {
        if (row.status_label) return row.status_label;
        if (row.status === 'late') return 'Đi muộn';
        if (row.status === 'present') return 'Đúng giờ';
        return '—';
    }

    function rowHtml(row) {
        const statusClass = row.status === 'late' ? 'ht-chip-warning' : 'ht-chip-success';
        const time = row.checked_in_at ? new Date(row.checked_in_at).toLocaleTimeString('vi-VN') : '';
        return `
            <tr data-id="${row.id}">
                <td class="font-mono text-xs">${row.student_code ?? ''}</td>
                <td>${row.student_name ?? ''}</td>
                <td><span class="${statusClass}">${statusLabelVi(row)}</span></td>
                <td class="text-xs text-gray-500">${time}</td>
            </tr>
        `;
    }

    try {
        await renderLive();
    } catch (error) {
        showToast(error.message, 'error');
    }

    refreshBtn?.addEventListener('click', async () => {
        try {
            await adminApi.refreshQr(sessionId);
            await renderLive();
            showToast('Đã làm mới mã QR (thời hạn buổi không đổi).');
        } catch (error) {
            showToast(error.message, 'error');
        }
    });

    copyBtn?.addEventListener('click', async () => {
        if (!lastCheckInUrl) return;
        try {
            await navigator.clipboard.writeText(lastCheckInUrl);
            showToast('Đã sao chép link điểm danh.');
        } catch {
            showToast(lastCheckInUrl);
        }
    });

    if (window.Echo) {
        window.Echo.channel(`attendance-sessions.${sessionId}`)
            .listen('.attendance.checked-in', (payload) => {
                if (tbody.querySelector(`[data-id="${payload.id}"]`)) return;
                tbody.insertAdjacentHTML('afterbegin', rowHtml(payload));
                countEl.textContent = String(tbody.querySelectorAll('tr').length);
            });
    }
}
