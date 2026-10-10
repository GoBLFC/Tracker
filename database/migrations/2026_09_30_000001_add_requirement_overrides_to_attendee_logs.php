<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	/**
	 * Run the migrations.
	 */
	public function up(): void {
		Schema::table('attendee_logs', function (Blueprint $table) {
			$table->boolean('gatekeepers_can_override')->default(false)->after('min_volunteer_hours');
		});

		Schema::table('attendee_log_user', function (Blueprint $table) {
			$table->foreignUuid('overridden_by_id')->nullable()->after('type')->constrained('users')->nullOnDelete();
			$table->string('override_reason', 255)->nullable()->after('overridden_by_id');
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void {
		Schema::table('attendee_log_user', function (Blueprint $table) {
			$table->dropColumn('override_reason');
			$table->dropConstrainedForeignId('overridden_by_id');
		});

		Schema::table('attendee_logs', function (Blueprint $table) {
			$table->dropColumn('gatekeepers_can_override');
		});
	}
};
