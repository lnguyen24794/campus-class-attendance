/** Matches admin-frontend PAGINATION.ITEMS_PER_PAGE_OPTIONS */
export const PAGE_SIZE_OPTIONS = [10, 20, 50];

const ICON_FIRST = `<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M18.41 16.59L13.82 12l4.59-4.59L17 6l-6 6 6 6zM6 6h2v12H6z"/></svg>`;
const ICON_PREV = `<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z"/></svg>`;
const ICON_NEXT = `<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M10 6L8.59 7.41 13.17 12l-4.58 4.59L10 18l6-6z"/></svg>`;
const ICON_LAST = `<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M5.59 7.41L10.18 12l-4.59 4.59L7 18l6-6-6-6zM16 6h2v12h-2z"/></svg>`;

/**
 * Admin-frontend style footer: per-page select + "from-to của total" + first/prev/next/last.
 *
 * @param {HTMLElement|null} el
 * @param {{ current_page?: number, last_page?: number, total?: number, from?: number|null, to?: number|null, per_page?: number }} meta
 * @param {{ onPage: (page: number) => void, onPerPage: (limit: number) => void, perPage?: number }} handlers
 */
export function renderPagination(el, meta, handlers) {
    if (!el) return;

    const { onPage, onPerPage } = handlers;
    const current = Number(meta?.current_page ?? 1);
    const last = Math.max(1, Number(meta?.last_page ?? 1));
    const total = Number(meta?.total ?? 0);
    const from = meta?.from ?? (total ? 0 : null);
    const to = meta?.to ?? (total ? 0 : null);
    const perPage = Number(handlers.perPage ?? meta?.per_page ?? 10);
    const isFirst = current <= 1;
    const isLast = current >= last;

    if (total === 0) {
        el.innerHTML = '';
        return;
    }

    const rangeText = `${from}-${to} của ${total}`;
    const options = PAGE_SIZE_OPTIONS.includes(perPage)
        ? PAGE_SIZE_OPTIONS
        : [...PAGE_SIZE_OPTIONS, perPage].sort((a, b) => a - b);

    el.innerHTML = `
        <div class="ht-pagination">
            <div class="ht-pagination-per-page">
                <span class="ht-pagination-label">Số mục mỗi trang:</span>
                <select class="ht-pagination-select" aria-label="Số mục mỗi trang">
                    ${options.map((n) => `<option value="${n}" ${n === perPage ? 'selected' : ''}>${n}</option>`).join('')}
                </select>
            </div>
            <span class="ht-pagination-range">${rangeText}</span>
            <div class="ht-pagination-nav">
                <button type="button" class="ht-page-btn" data-page="1" ${isFirst ? 'disabled' : ''} aria-label="Trang đầu">${ICON_FIRST}</button>
                <button type="button" class="ht-page-btn" data-page="${current - 1}" ${isFirst ? 'disabled' : ''} aria-label="Trang trước">${ICON_PREV}</button>
                <button type="button" class="ht-page-btn" data-page="${current + 1}" ${isLast ? 'disabled' : ''} aria-label="Trang sau">${ICON_NEXT}</button>
                <button type="button" class="ht-page-btn" data-page="${last}" ${isLast ? 'disabled' : ''} aria-label="Trang cuối">${ICON_LAST}</button>
            </div>
        </div>
    `;

    el.querySelectorAll('[data-page]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const page = Number(btn.getAttribute('data-page'));
            if (!Number.isFinite(page) || page < 1 || page > last || page === current) return;
            onPage(page);
        });
    });

    el.querySelector('.ht-pagination-select')?.addEventListener('change', (event) => {
        const limit = Number(event.target.value);
        if (!Number.isFinite(limit) || limit < 1) return;
        onPerPage(limit);
    });
}
