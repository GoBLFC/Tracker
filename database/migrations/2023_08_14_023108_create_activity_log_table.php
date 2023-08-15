<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateActivityLogTable extends Migration {
	public function up() {
		Schema::connection(config('activitylog.database_connection'))->create(config('activitylog.table_name'), function (Blueprint $table) {
			$table->uuid('id')->primary();
			$table->string('log_name')->nullable()->index();
			$table->text('description');
			$table->nullableUuidMorphs('subject', 'subject');
			$table->nullableUuidMorphs('causer', 'causer');
			$table->json('properties')->nullable();
			$table->timestamps();
		});
	}

	public function down() {
		Schema::connection(config('activitylog.database_connection'))->dropIfExists(config('activitylog.table_name'));
	}
}
