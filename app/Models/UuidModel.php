<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

/**
 * @property string $id
 */
class UuidModel extends Model {
	use HasUuids;

	public function newUniqueId(): string {
		return (string) Uuid::uuid7();
	}
}
