<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class ManagementController extends Controller {
	public function getLeadIndex(): View {
		return view('management.lead');
	}
}
