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

class StudentController extends Controller
{
    public function __construct(
        private MasterUserService $masterUserService,
    ) {}

    /**
     * Display a listing of students.
     */
    public function index(Request $request): View
    {
        $students = $this->masterUserService->fetchUserList(UserRole::Student, [
            'q' => $request->string('q')->toString(),
            'limit' => 20,
        ]);

        return view('master.students.index', compact('students'));
    }

    /**
     * Show the form for creating a student.
     */
    public function create(): View
    {
        return view('master.students.create');
    }

    /**
     * Store a newly created student.
     */
    public function store(Request $request): RedirectResponse
    {
        $params = $this->validated($request);

        $this->masterUserService->createUser(UserRole::Student, $params);

        return redirect()
            ->route('students.index')
            ->with('success', 'Đã thêm sinh viên.');
    }

    /**
     * Show the form for editing a student.
     */
    public function edit(User $student): View
    {
        abort_unless($student->isStudent(), 404);

        return view('master.students.edit', compact('student'));
    }

    /**
     * Update the specified student.
     */
    public function update(Request $request, User $student): RedirectResponse
    {
        abort_unless($student->isStudent(), 404);

        $params = $this->validated($request, $student);

        $this->masterUserService->updateUser($student, UserRole::Student, $params);

        return redirect()
            ->route('students.index')
            ->with('success', 'Đã cập nhật sinh viên.');
    }

    /**
     * Remove the specified student.
     */
    public function destroy(User $student): RedirectResponse
    {
        abort_unless($student->isStudent(), 404);

        $this->masterUserService->deleteUser($student, UserRole::Student);

        return redirect()
            ->route('students.index')
            ->with('success', 'Đã xóa sinh viên.');
    }

    /**
     * @return array{name: string, email: string, code: string, password?: string|null}
     */
    private function validated(Request $request, ?User $student = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($student?->id)],
            'code' => ['required', 'string', 'max:32', Rule::unique('users', 'code')->ignore($student?->id)],
            'password' => [$student ? 'nullable' : 'required', 'string', 'min:6'],
        ]);
    }
}
