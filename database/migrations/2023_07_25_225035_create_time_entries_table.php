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
			$table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
			$table->timestamp('start');
			$table->timestamp('stop')->nullable();
			$table->foreignUuid('department_id')->constrained()->cascadeOnDelete();
			$table->text('notes')->nullable();
			$table->foreignUuid('creator_user_id')->nullable()->constrained('users')->nullOnDelete();
			$table->boolean('auto')->default(false);
			$table->foreignUuid('event_id')->constrained()->cascadeOnDelete();
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
