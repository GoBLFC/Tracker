<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
	/**
	 * Run the migrations.
	 */
	public function up(): void {
		// Update the role of all currently-banned users
		DB::table('users')->whereRole(-1)->update(['role' => -2]);
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void {
		// Update the role of attendees to volunteer
		DB::table('users')->whereRole(-1)->update(['role' => 0]);

		// Update the role of all banned users
		DB::table('users')->whereRole(-2)->update(['role' => -1]);
	}
};
