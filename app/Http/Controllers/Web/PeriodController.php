<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Period;
use App\Services\PeriodService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PeriodController extends Controller
{
    public function __construct(
        private PeriodService $periodService,
    ) {}

    /**
     * Display a listing of periods.
     */
    public function index(Request $request): View
    {
        $periods = $this->periodService->fetchPeriodList([
            'q' => $request->string('q')->toString(),
            'limit' => 20,
        ]);

        return view('master.periods.index', compact('periods'));
    }

    /**
     * Show the form for creating a period.
     */
    public function create(): View
    {
        return view('master.periods.create');
    }

    /**
     * Store a newly created period.
     */
    public function store(Request $request): RedirectResponse
    {
        $params = $this->validated($request);

        $this->periodService->createPeriod($params);

        return redirect()
            ->route('periods.index')
            ->with('success', 'Đã thêm tiết học.');
    }

    /**
     * Show the form for editing a period.
     */
    public function edit(Period $period): View
    {
        return view('master.periods.edit', compact('period'));
    }

    /**
     * Update the specified period.
     */
    public function update(Request $request, Period $period): RedirectResponse
    {
        $params = $this->validated($request, $period);

        $this->periodService->updatePeriod($period, $params);

        return redirect()
            ->route('periods.index')
            ->with('success', 'Đã cập nhật tiết học.');
    }

    /**
     * Remove the specified period.
     */
    public function destroy(Period $period): RedirectResponse
    {
        $this->periodService->deletePeriod($period);

        return redirect()
            ->route('periods.index')
            ->with('success', 'Đã xóa tiết học.');
    }

    /**
     * @return array{number: int, name: string, starts_at: string, ends_at: string}
     */
    private function validated(Request $request, ?Period $period = null): array
    {
        return $request->validate([
            'number' => ['required', 'integer', 'min:1', 'max:20', Rule::unique('periods', 'number')->ignore($period?->id)],
            'name' => ['required', 'string', 'max:255'],
            'starts_at' => ['required', 'date_format:H:i'],
            'ends_at' => ['required', 'date_format:H:i', 'after:starts_at'],
        ]);
    }
}
