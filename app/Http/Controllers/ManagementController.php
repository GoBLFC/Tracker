<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Department;
use App\Models\Reward;
use App\Models\TimeEntry;
use Illuminate\View\View;

class ManagementController extends Controller {
	/**
	 * Render the lead panel
	 */
	public function getLeadIndex(): View {
		return view('management.lead');
	}

	/**
	 * Render the management panel
	 */
	public function getManagerIndex(): View {
		return view('management.manager', [
			'rewards' => Reward::forEvent()->orderBy('hours')->get(),
			'departments' => Department::orderBy('hidden')->orderBy('name')->get(),
			'longestOngoingEntries' => TimeEntry::with(['user', 'department'])
				->forEvent()
				->ongoing()
				->orderBy('start')
				->limit(10)
				->get(),
			'recentTimeActivities' => Activity::with(['subject', 'subject.user'])
				->has('subject')
				->whereSubjectType(TimeEntry::class)
				->orderByDesc('created_at')
				->limit(10)
				->get()
		]);
	}
}
