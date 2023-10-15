<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	/**
	 * Run the migrations.
	 */
	public function up(): void {
		Schema::create('attendee_log_user', function (Blueprint $table) {
			$table->foreignUuid('attendee_log_id')->constrained()->cascadeOnDelete();
			$table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
			$table->enum('type', ['attendee', 'gatekeeper'])->default('attendee');
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void {
		Schema::dropIfExists('attendee_log_user');
	}
};
