<?php

namespace App\Http\Controllers\Web;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\MasterUserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LecturerController extends Controller
{
    public function __construct(
        private MasterUserService $masterUserService,
    ) {}

    /**
     * Display a listing of lecturers.
     */
    public function index(Request $request): View
    {
        $lecturers = $this->masterUserService->fetchUserList(UserRole::Lecturer, [
            'q' => $request->string('q')->toString(),
            'limit' => 20,
        ]);

        return view('master.lecturers.index', compact('lecturers'));
    }

    /**
     * Show the form for creating a lecturer.
     */
    public function create(): View
    {
        return view('master.lecturers.create');
    }

    /**
     * Store a newly created lecturer.
     */
    public function store(Request $request): RedirectResponse
    {
        $params = $this->validated($request);

        $this->masterUserService->createUser(UserRole::Lecturer, $params);

        return redirect()
            ->route('lecturers.index')
            ->with('success', 'Đã thêm giảng viên.');
    }

    /**
     * Show the form for editing a lecturer.
     */
    public function edit(User $lecturer): View
    {
        abort_unless($lecturer->isLecturer(), 404);

        return view('master.lecturers.edit', compact('lecturer'));
    }

    /**
     * Update the specified lecturer.
     */
    public function update(Request $request, User $lecturer): RedirectResponse
    {
        abort_unless($lecturer->isLecturer(), 404);

        $params = $this->validated($request, $lecturer);

        $this->masterUserService->updateUser($lecturer, UserRole::Lecturer, $params);

        return redirect()
            ->route('lecturers.index')
            ->with('success', 'Đã cập nhật giảng viên.');
    }

    /**
     * Remove the specified lecturer.
     */
    public function destroy(User $lecturer): RedirectResponse
    {
        abort_unless($lecturer->isLecturer(), 404);

        $this->masterUserService->deleteUser($lecturer, UserRole::Lecturer);

        return redirect()
            ->route('lecturers.index')
            ->with('success', 'Đã xóa giảng viên.');
    }

    /**
     * @return array{name: string, email: string, code: string, password?: string|null}
     */
    private function validated(Request $request, ?User $lecturer = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($lecturer?->id)],
            'code' => ['required', 'string', 'max:32', Rule::unique('users', 'code')->ignore($lecturer?->id)],
            'password' => [$lecturer ? 'nullable' : 'required', 'string', 'min:6'],
        ]);
    }
}
