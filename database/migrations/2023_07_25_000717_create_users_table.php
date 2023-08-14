<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	/**
	 * Run the migrations.
	 */
	public function up(): void {
		Schema::create('users', function (Blueprint $table) {
			$table->bigInteger('id')->unsigned()->primary();
			$table->string('username', 64);
			$table->string('first_name', 64);
			$table->string('last_name', 64);
			$table->string('badge_name', 64)->nullable();
			$table->tinyInteger('role')->unsigned()->default(0);
			$table->char('tg_setup_key', 32)->unique();
			$table->bigInteger('tg_chat_id')->unsigned()->nullable()->unique();
			$table->timestamps();
			$table->softDeletes();
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void {
		Schema::dropIfExists('users');
	}
};
