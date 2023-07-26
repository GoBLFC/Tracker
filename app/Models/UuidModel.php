<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class UuidModel extends Model {
	use HasUuids;

	public function newUniqueId(): string {
		return (string) Uuid::uuid7();
	}
}
