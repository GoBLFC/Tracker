<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	/**
	 * Run the migrations.
	 */
	public function up(): void {
		// This migration is only for Postgres since it can take advantage of expressions in indexes
		$connection = config('database.default');
		$driver = config("database.connections.{$connection}.driver");
		if ($driver !== 'pgsql') return;

		Schema::table('users', function (Blueprint $table) {
			$table->rawIndex('(lower(username))', 'users_username_lc_index');
			$table->rawIndex('(lower(badge_name))', 'users_badge_name_lc_index');
			$table->rawIndex('(lower(first_name))', 'users_first_name_lc_index');
			$table->rawIndex('(lower(last_name))', 'users_last_name_lc_index');
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void {
		// This migration is only for Postgres since it can take advantage of expressions in indexes
		$connection = config('database.default');
		$driver = config("database.connections.{$connection}.driver");
		if ($driver !== 'pgsql') return;

		Schema::table('users', function (Blueprint $table) {
			$table->dropIndex('users_username_lc_index');
			$table->dropIndex('users_badge_name_lc_index');
			$table->dropIndex('users_first_name_lc_index');
			$table->dropIndex('users_last_name_lc_index');
		});
	}
};
