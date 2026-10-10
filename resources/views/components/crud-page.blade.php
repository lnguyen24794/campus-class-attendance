@props([
    'title',
    'breadcrumb',
    'columns' => [],
    'fields' => [],
    'searchTitle' => 'Tìm kiếm',
])

<div class="ht-page">
    {{-- Primary add — admin-frontend projects List --}}
    <section class="ht-card p-4">
        <button type="button" id="crud-add-btn" class="ht-btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Thêm mới
        </button>
    </section>

    {{-- Collapsible search — ProjectSearch.vue --}}
    <section class="ht-card p-4">
        <button type="button" id="crud-search-toggle" class="ht-btn-primary" aria-expanded="false">
            <svg id="crud-search-chevron" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
            {{ $searchTitle }}
        </button>

        <div id="crud-search-panel" class="mt-5 hidden">
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div>
                    <label for="crud-search" class="ht-label">Tìm kiếm</label>
                    <input type="text" id="crud-search" class="ht-input" placeholder="Tìm theo mã, tên…">
                </div>
            </div>
            <div class="mt-5 flex flex-wrap items-center justify-center gap-2">
                <button type="button" id="crud-search-btn" class="ht-btn-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    Tìm kiếm
                </button>
                <button type="button" id="crud-clear-btn" class="ht-btn-secondary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    Hủy
                </button>
            </div>
        </div>
    </section>

    {{-- Data table --}}
    <section class="ht-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="ht-table min-w-full">
                <thead>
                    <tr>
                        <th class="w-14">STT</th>
                        @foreach ($columns as $column)
                            <th>{{ $column }}</th>
                        @endforeach
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody id="crud-tbody"></tbody>
            </table>
        </div>
        <div id="crud-pagination" class="border-t border-gray-200 px-4 py-3"></div>
    </section>
</div>

{{-- Create / Edit modal --}}
<div id="crud-modal" tabindex="-1" aria-hidden="true"
     class="fixed left-0 right-0 top-0 z-50 hidden h-[calc(100%-1rem)] max-h-full w-full items-center justify-center overflow-y-auto overflow-x-hidden md:inset-0">
    <div class="relative max-h-full w-full max-w-lg p-4">
        <div class="relative rounded-lg border border-gray-200 bg-white shadow-lg">
            <div class="flex items-start justify-between border-b border-gray-200 p-4">
                <h3 id="crud-modal-title" class="text-lg font-semibold text-gray-800">Thêm mới</h3>
                <button type="button" data-modal-hide="crud-modal"
                        class="rounded-md p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-700">
                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                </button>
            </div>
            <form id="crud-form" class="space-y-4 p-5">
                @foreach ($fields as $field)
                    <div>
                        <label for="{{ $field['name'] }}" class="ht-label">{{ $field['label'] }}</label>
                        @if (($field['type'] ?? 'text') === 'textarea')
                            <textarea id="{{ $field['name'] }}" name="{{ $field['name'] }}" rows="3" class="ht-input"></textarea>
                        @else
                            <input id="{{ $field['name'] }}" name="{{ $field['name'] }}" type="{{ $field['type'] ?? 'text' }}"
                                   @if(!empty($field['required'])) required @endif
                                   class="ht-input">
                        @endif
                    </div>
                @endforeach
                <div class="flex items-center gap-2 border-t border-gray-200 pt-4">
                    <button type="submit" class="ht-btn-primary">Lưu</button>
                    <button type="button" data-modal-hide="crud-modal" class="ht-btn-secondary">Hủy</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Delete confirm modal --}}
<div id="crud-delete-modal" tabindex="-1" class="fixed left-0 right-0 top-0 z-50 hidden h-modal w-full items-center justify-center overflow-y-auto overflow-x-hidden md:inset-0 md:h-full">
    <div class="relative h-full w-full max-w-md p-4 md:h-auto">
        <div class="relative rounded-lg border border-gray-200 bg-white p-5 text-center shadow-lg">
            <p class="mb-4 text-gray-600">Bạn chắc chắn muốn xóa bản ghi này?</p>
            <div class="flex items-center justify-center gap-3">
                <button data-modal-hide="crud-delete-modal" type="button" class="ht-btn-secondary">Hủy</button>
                <button id="crud-confirm-delete" type="button" class="rounded bg-red-600 px-3.5 py-2 text-sm font-medium text-white hover:bg-red-700">
                    Xóa
                </button>
            </div>
        </div>
    </div>
</div>
