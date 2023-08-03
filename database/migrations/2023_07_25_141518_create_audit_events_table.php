<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	/**
	 * Run the migrations.
	 */
	public function up(): void {
		Schema::create('audit_events', function (Blueprint $table) {
			$table->uuid('id')->primary();
			$table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
			$table->string('action', 64);
			$table->text('data');
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void {
		Schema::dropIfExists('audit_events');
	}
};
