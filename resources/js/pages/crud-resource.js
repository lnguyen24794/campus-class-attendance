import { Modal } from 'flowbite';
import { adminApi, ApiError } from '../api';
import { iconEdit, iconTrash } from '../ui/icons';
import { PAGE_SIZE_OPTIONS, renderPagination } from '../ui/pagination';
import { fieldErrors, showToast } from '../ui/toast';

/**
 * Generic Flowbite-style CRUD table driven by /api/v1/admin/{resource}.
 */
export function initCrudPage(config) {
    const {
        resource,
        columns,
        formFields,
        titleSingular,
    } = config;

    const tbody = document.getElementById('crud-tbody');
    const searchInput = document.getElementById('crud-search');
    const searchBtn = document.getElementById('crud-search-btn');
    const clearBtn = document.getElementById('crud-clear-btn');
    const searchToggle = document.getElementById('crud-search-toggle');
    const searchPanel = document.getElementById('crud-search-panel');
    const searchChevron = document.getElementById('crud-search-chevron');
    const addBtn = document.getElementById('crud-add-btn');
    const form = document.getElementById('crud-form');
    const modalEl = document.getElementById('crud-modal');
    const modalTitle = document.getElementById('crud-modal-title');
    const deleteModalEl = document.getElementById('crud-delete-modal');
    const confirmDeleteBtn = document.getElementById('crud-confirm-delete');
    const paginationEl = document.getElementById('crud-pagination');

    if (!tbody || !form || !modalEl) {
        return;
    }

    const modal = new Modal(modalEl);
    const deleteModal = deleteModalEl ? new Modal(deleteModalEl) : null;
    let editingId = null;
    let deletingId = null;
    let searchTimer = null;
    let currentPage = 1;
    let currentQ = '';
    let perPage = PAGE_SIZE_OPTIONS[0];

    async function load(page = currentPage, q = currentQ) {
        currentPage = page;
        currentQ = q;
        const colSpan = columns.length + 2;
        tbody.innerHTML = `<tr><td colspan="${colSpan}" class="px-4 py-8 text-center text-gray-500">Đang tải…</td></tr>`;
        try {
            const res = await adminApi.list(resource, { q, page, limit: perPage });
            const items = res.data.items ?? [];
            const meta = res.data.meta ?? {};

            renderPagination(paginationEl, meta, {
                perPage,
                onPage: (nextPage) => load(nextPage, currentQ),
                onPerPage: (limit) => {
                    perPage = limit;
                    load(1, currentQ);
                },
            });

            if (!items.length) {
                tbody.innerHTML = `<tr><td colspan="${colSpan}" class="px-4 py-8 text-center text-gray-500">Chưa có dữ liệu.</td></tr>`;
                return;
            }

            const offset = ((meta.current_page ?? 1) - 1) * (meta.per_page ?? perPage);

            tbody.innerHTML = items.map((row, index) => `
                <tr data-id="${row.id}">
                    <td class="text-gray-500">${offset + index + 1}</td>
                    ${columns.map((col) => `<td>${escapeHtml(col.render ? col.render(row) : (row[col.key] ?? ''))}</td>`).join('')}
                    <td>
                        <div class="ht-actions">
                            <button type="button" data-action="edit" class="ht-action-btn">
                                ${iconEdit}<span>Sửa</span>
                            </button>
                            <button type="button" data-action="delete" class="ht-action-btn-danger">
                                ${iconTrash}<span>Xóa</span>
                            </button>
                        </div>
                    </td>
                </tr>
            `).join('');
        } catch (error) {
            tbody.innerHTML = `<tr><td colspan="${colSpan}" class="px-4 py-8 text-center text-red-600">${escapeHtml(error.message)}</td></tr>`;
            if (paginationEl) paginationEl.innerHTML = '';
        }
    }

    function fillForm(row = null) {
        formFields.forEach((field) => {
            const input = form.elements.namedItem(field.name);
            if (!input) return;
            input.value = row ? (row[field.name] ?? '') : (field.defaultValue ?? '');
            if (field.name === 'password') {
                input.required = !row;
                input.placeholder = row ? 'Để trống nếu giữ nguyên' : '';
            }
        });
    }

    function openCreate() {
        editingId = null;
        modalTitle.textContent = `Thêm ${titleSingular}`;
        fillForm(null);
        modal.show();
    }

    async function openEdit(id) {
        editingId = id;
        modalTitle.textContent = `Sửa ${titleSingular}`;
        try {
            const res = await adminApi.get(resource, id);
            fillForm(res.data);
            modal.show();
        } catch (error) {
            showToast(error.message, 'error');
        }
    }

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        const body = Object.fromEntries(new FormData(form).entries());
        if (editingId && !body.password) {
            delete body.password;
        }
        if (body.credits) body.credits = Number(body.credits);
        if (body.number) body.number = Number(body.number);

        try {
            if (editingId) {
                await adminApi.update(resource, editingId, body);
                showToast('Đã cập nhật.');
            } else {
                await adminApi.create(resource, body);
                showToast('Đã tạo mới.');
            }
            modal.hide();
            await load(1, searchInput?.value.trim() ?? '');
        } catch (error) {
            const msg = error instanceof ApiError
                ? (fieldErrors(error.payload) || error.message)
                : error.message;
            showToast(msg, 'error');
        }
    });

    tbody.addEventListener('click', (event) => {
        const btn = event.target.closest('[data-action]');
        const row = event.target.closest('tr[data-id]');
        if (!btn || !row) return;
        const id = row.dataset.id;
        if (btn.dataset.action === 'edit') openEdit(id);
        if (btn.dataset.action === 'delete') {
            deletingId = id;
            deleteModal?.show();
        }
    });

    confirmDeleteBtn?.addEventListener('click', async () => {
        if (!deletingId) return;
        try {
            await adminApi.destroy(resource, deletingId);
            showToast('Đã xóa.');
            deleteModal?.hide();
            deletingId = null;
            await load(currentPage, currentQ);
        } catch (error) {
            showToast(error.message, 'error');
        }
    });

    addBtn?.addEventListener('click', openCreate);

    const runSearch = () => load(1, searchInput?.value.trim() ?? '');

    searchToggle?.addEventListener('click', () => {
        if (!searchPanel) return;
        const open = searchPanel.classList.toggle('hidden') === false;
        searchToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        if (searchChevron) {
            searchChevron.innerHTML = open
                ? '<polyline points="18 15 12 9 6 15"/>'
                : '<polyline points="6 9 12 15 18 9"/>';
        }
    });

    searchBtn?.addEventListener('click', runSearch);
    clearBtn?.addEventListener('click', () => {
        if (searchInput) searchInput.value = '';
        load(1, '');
    });
    searchInput?.addEventListener('keydown', (event) => {
        if (event.key === 'Enter') {
            event.preventDefault();
            runSearch();
        }
    });
    searchInput?.addEventListener('input', () => {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(runSearch, 300);
    });

    document.querySelectorAll('[data-modal-hide="crud-modal"]').forEach((el) => {
        el.addEventListener('click', () => modal.hide());
    });
    document.querySelectorAll('[data-modal-hide="crud-delete-modal"]').forEach((el) => {
        el.addEventListener('click', () => deleteModal?.hide());
    });

    load(1, '');
}

function escapeHtml(value) {
    return String(value)
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;');
}
