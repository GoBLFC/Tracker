<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	/**
	 * Run the migrations.
	 */
	public function up(): void {
		Schema::create('time_entries', function (Blueprint $table) {
			$table->uuid('id')->primary();
			$table->foreignId('user_id')->constrained();
			$table->timestamp('start');
			$table->timestamp('stop')->nullable();
			$table->foreignUuid('department_id')->constrained();
			$table->text('notes')->nullable();
			$table->foreignId('creator_user_id')->constrained('users');
			$table->boolean('auto')->default(false);
			$table->foreignUuid('event_id')->constrained();
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void {
		Schema::dropIfExists('time_entries');
	}
};
