import 'flowbite';
import './echo';
import { initCrudPage } from './pages/crud-resource';
import { initDashboardPage } from './pages/dashboard';
import { initSessionCreatePage, initSessionLivePage } from './pages/sessions';
import { initSessionsHistoryPage } from './pages/sessions-history';
import { initCheckInPage } from './pages/check-in';

document.addEventListener('DOMContentLoaded', () => {
    const page = document.body.dataset.page;

    if (page === 'dashboard') {
        initDashboardPage();
    }

    if (page === 'students') {
        initCrudPage({
            resource: 'students',
            titleSingular: 'sinh viên',
            columns: [
                { key: 'code' },
                { key: 'name' },
                { key: 'email' },
            ],
            formFields: [
                { name: 'code' },
                { name: 'name' },
                { name: 'email' },
                { name: 'password' },
            ],
        });
    }

    if (page === 'lecturers') {
        initCrudPage({
            resource: 'lecturers',
            titleSingular: 'giảng viên',
            columns: [
                { key: 'code' },
                { key: 'name' },
                { key: 'email' },
            ],
            formFields: [
                { name: 'code' },
                { name: 'name' },
                { name: 'email' },
                { name: 'password' },
            ],
        });
    }

    if (page === 'subjects') {
        initCrudPage({
            resource: 'subjects',
            titleSingular: 'môn học',
            columns: [
                { key: 'code' },
                { key: 'name' },
                { key: 'credits' },
            ],
            formFields: [
                { name: 'code' },
                { name: 'name' },
                { name: 'credits', defaultValue: 3 },
                { name: 'description' },
            ],
        });
    }

    if (page === 'periods') {
        initCrudPage({
            resource: 'periods',
            titleSingular: 'tiết học',
            columns: [
                { key: 'number' },
                { key: 'name' },
                { key: 'starts_at' },
                { key: 'ends_at' },
            ],
            formFields: [
                { name: 'number' },
                { name: 'name' },
                { name: 'starts_at', defaultValue: '07:00' },
                { name: 'ends_at', defaultValue: '07:50' },
            ],
        });
    }

    if (page === 'sessions-history') {
        initSessionsHistoryPage();
    }

    if (page === 'sessions-create') {
        initSessionCreatePage();
    }

    if (page === 'sessions-live') {
        initSessionLivePage();
    }

    if (page === 'check-in') {
        initCheckInPage();
    }
});
