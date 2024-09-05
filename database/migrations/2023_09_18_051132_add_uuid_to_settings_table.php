<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Ramsey\Uuid\Uuid;

return new class extends Migration {
	/**
	 * Run the migrations.
	 */
	public function up(): void {
		// Rename the existing ID column to name, then add the new ID column.
		// These are separate calls to Schema::table because they involve columns of the same name.
		Schema::table('settings', function (Blueprint $table) {
			$table->renameColumn('id', 'name');
			$table->dropPrimary();
		});
		Schema::table('settings', function (Blueprint $table) {
			$table->uuid('id')->nullable()->first();
			$table->string('name', 32)->unique()->change();
		});

		// Add UUIDs to each existing row
		$rows = DB::table('settings')->select('name')->get();
		foreach ($rows as $row) {
			DB::table('settings')
				->where('name', $row->name)
				->update(['id' => Uuid::uuid7()]);
		}

		// Make the new ID column non-nullable and primary
		Schema::table('settings', function (Blueprint $table) {
			$table->uuid('id')->primary()->nullable(false)->change();
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void {
		Schema::table('settings', function (Blueprint $table) {
			$table->dropColumn('id');
		});
		Schema::table('settings', function (Blueprint $table) {
			$table->renameColumn('name', 'id');
			$table->dropUnique('settings_name_unique');
		});
		Schema::table('settings', function (Blueprint $table) {
			$table->string('id', 32)->primary()->change();
		});
	}
};
