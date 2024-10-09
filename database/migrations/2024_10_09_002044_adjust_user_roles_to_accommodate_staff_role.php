<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
	/**
	 * Run the migrations.
	 */
	public function up(): void {
		// Update the role of all users above volunteer
		DB::table('users')->whereRole(3)->update(['role' => 4]);
		DB::table('users')->whereRole(2)->update(['role' => 3]);
		DB::table('users')->whereRole(1)->update(['role' => 2]);
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void {
		// Update the role of staff to volunteer
		DB::table('users')->whereRole(1)->update(['role' => 0]);

		// Update the role of all users above volunteer
		DB::table('users')->whereRole(2)->update(['role' => 1]);
		DB::table('users')->whereRole(3)->update(['role' => 2]);
		DB::table('users')->whereRole(4)->update(['role' => 3]);
	}
};
