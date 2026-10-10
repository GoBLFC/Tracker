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
			$table->json('allowed_registration_levels')->nullable()->after('event_id');
			$table->boolean('allow_staff')->default(false)->after('allowed_registration_levels');
			$table->decimal('min_volunteer_hours', 5, 2)->nullable()->after('allow_staff');
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void {
		Schema::table('attendee_logs', function (Blueprint $table) {
			$table->dropColumn(['allowed_registration_levels', 'allow_staff', 'min_volunteer_hours']);
		});
	}
};
