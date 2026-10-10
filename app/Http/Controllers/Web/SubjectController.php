<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use App\Services\SubjectService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SubjectController extends Controller
{
    public function __construct(
        private SubjectService $subjectService,
    ) {}

    /**
     * Display a listing of subjects.
     */
    public function index(Request $request): View
    {
        $subjects = $this->subjectService->fetchSubjectList([
            'q' => $request->string('q')->toString(),
            'limit' => 20,
        ]);

        return view('master.subjects.index', compact('subjects'));
    }

    /**
     * Show the form for creating a subject.
     */
    public function create(): View
    {
        return view('master.subjects.create');
    }

    /**
     * Store a newly created subject.
     */
    public function store(Request $request): RedirectResponse
    {
        $params = $this->validated($request);

        $this->subjectService->createSubject($params);

        return redirect()
            ->route('subjects.index')
            ->with('success', 'Đã thêm môn học.');
    }

    /**
     * Show the form for editing a subject.
     */
    public function edit(Subject $subject): View
    {
        return view('master.subjects.edit', compact('subject'));
    }

    /**
     * Update the specified subject.
     */
    public function update(Request $request, Subject $subject): RedirectResponse
    {
        $params = $this->validated($request, $subject);

        $this->subjectService->updateSubject($subject, $params);

        return redirect()
            ->route('subjects.index')
            ->with('success', 'Đã cập nhật môn học.');
    }

    /**
     * Remove the specified subject.
     */
    public function destroy(Subject $subject): RedirectResponse
    {
        $this->subjectService->deleteSubject($subject);

        return redirect()
            ->route('subjects.index')
            ->with('success', 'Đã xóa môn học.');
    }

    /**
     * @return array{code: string, name: string, credits: int, description?: string|null}
     */
    private function validated(Request $request, ?Subject $subject = null): array
    {
        return $request->validate([
            'code' => ['required', 'string', 'max:32', Rule::unique('subjects', 'code')->ignore($subject?->id)],
            'name' => ['required', 'string', 'max:255'],
            'credits' => ['required', 'integer', 'min:1', 'max:10'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);
    }
}
