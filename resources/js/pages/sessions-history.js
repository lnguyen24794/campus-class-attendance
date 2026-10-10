import { adminApi } from '../api';
import { iconEye } from '../ui/icons';
import { PAGE_SIZE_OPTIONS, renderPagination } from '../ui/pagination';
import { showToast } from '../ui/toast';

function escapeHtml(value) {
    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;');
}

function formatDateTime(iso) {
    if (!iso) return '—';
    return new Date(iso).toLocaleString('vi-VN');
}

function statusChip(row) {
    const label = row.status_label
        ?? (row.status === 'open' ? 'Đang mở' : row.status === 'closed' ? 'Đã đóng' : '—');
    const cls = row.status === 'open' ? 'ht-chip-success' : 'ht-chip-muted';
    return `<span class="${cls}">${escapeHtml(label)}</span>`;
}

export async function initSessionsHistoryPage() {
    const root = document.getElementById('sessions-history-root');
    if (!root) return;

    const tbody = document.getElementById('sessions-history-tbody');
    const paginationEl = document.getElementById('sessions-history-pagination');
    const qInput = document.getElementById('session-search-q');
    const statusSelect = document.getElementById('session-search-status');
    const searchBtn = document.getElementById('session-search-btn');
    const clearBtn = document.getElementById('session-search-clear');
    const toggle = document.getElementById('session-search-toggle');
    const panel = document.getElementById('session-search-panel');
    const chevron = document.getElementById('session-search-chevron');

    let currentPage = 1;
    let perPage = PAGE_SIZE_OPTIONS[0];

    async function load(page = currentPage) {
        currentPage = page;
        tbody.innerHTML = `<tr><td colspan="8" class="px-4 py-8 text-center text-gray-500">Đang tải…</td></tr>`;
        try {
            const res = await adminApi.listSessions({
                q: qInput?.value.trim() ?? '',
                status: statusSelect?.value ?? '',
                page,
                limit: perPage,
            });
            const items = res.data.items ?? [];
            const meta = res.data.meta ?? {};

            renderPagination(paginationEl, meta, {
                perPage,
                onPage: load,
                onPerPage: (limit) => {
                    perPage = limit;
                    load(1);
                },
            });

            if (!items.length) {
                tbody.innerHTML = `<tr><td colspan="8" class="px-4 py-8 text-center text-gray-500">Chưa có lịch sử điểm danh.</td></tr>`;
                return;
            }

            const offset = ((meta.current_page ?? 1) - 1) * (meta.per_page ?? perPage);

            tbody.innerHTML = items.map((row, index) => `
                <tr>
                    <td class="text-gray-500">${offset + index + 1}</td>
                    <td class="font-medium text-gray-800">${escapeHtml(row.title)}</td>
                    <td>
                        <div>${escapeHtml(row.course_section_code ?? '—')}</div>
                        <div class="text-xs text-gray-500">${escapeHtml(row.course_section_name ?? '')}</div>
                    </td>
                    <td>${escapeHtml(row.period_name ?? '—')}</td>
                    <td class="text-xs">${escapeHtml(formatDateTime(row.opens_at))}</td>
                    <td>${row.attendances_count ?? 0}</td>
                    <td>${statusChip(row)}</td>
                    <td>
                        <div class="ht-actions">
                            <a href="${escapeHtml(row.live_url)}" class="ht-action-btn">
                                ${iconEye}<span>Xem</span>
                            </a>
                        </div>
                    </td>
                </tr>
            `).join('');
        } catch (error) {
            tbody.innerHTML = `<tr><td colspan="8" class="px-4 py-8 text-center text-red-600">${escapeHtml(error.message)}</td></tr>`;
            if (paginationEl) paginationEl.innerHTML = '';
            showToast(error.message, 'error');
        }
    }

    toggle?.addEventListener('click', () => {
        if (!panel) return;
        const hidden = panel.classList.toggle('hidden');
        toggle.setAttribute('aria-expanded', hidden ? 'false' : 'true');
        if (chevron) {
            chevron.innerHTML = hidden
                ? '<polyline points="6 9 12 15 18 9"/>'
                : '<polyline points="18 15 12 9 6 15"/>';
        }
    });

    const runSearch = () => load(1);
    searchBtn?.addEventListener('click', runSearch);
    clearBtn?.addEventListener('click', () => {
        if (qInput) qInput.value = '';
        if (statusSelect) statusSelect.value = '';
        load(1);
    });
    qInput?.addEventListener('keydown', (event) => {
        if (event.key === 'Enter') {
            event.preventDefault();
            runSearch();
        }
    });

    await load(1);
}
