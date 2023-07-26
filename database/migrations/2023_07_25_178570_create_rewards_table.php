<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	/**
	 * Run the migrations.
	 */
	public function up(): void {
		Schema::create('rewards', function (Blueprint $table) {
			$table->uuid('id')->primary();
			$table->string('name', 64);
			$table->text('description');
			$table->tinyInteger('hours');
			$table->foreignUuid('event_id')->constrained();
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void {
		Schema::dropIfExists('rewards');
	}
};
