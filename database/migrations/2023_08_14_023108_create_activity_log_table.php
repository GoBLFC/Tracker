<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateActivityLogTable extends Migration {
	public function up() {
		Schema::connection(config('activitylog.database_connection'))->create(config('activitylog.table_name'), function (Blueprint $table) {
			$table->uuid('id')->primary();
			$table->string('log_name')->nullable()->index();
			$table->text('description');
			$table->nullableUuidMorphs('subject', 'subject');
			$table->nullableUuidMorphs('causer', 'causer');
			$table->jsonb('properties')->nullable();
			$table->timestamps();
		});
	}

	public function down() {
		Schema::connection(config('activitylog.database_connection'))->dropIfExists(config('activitylog.table_name'));
	}
}
