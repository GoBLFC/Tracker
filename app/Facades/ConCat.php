<?php

namespace App\Facades;

use App\Services\ConCat\ConCatApiClient;
use Illuminate\Support\Facades\Facade;

/**
 * @mixin \App\Services\ConCat\ConCatApiClient
 */
class ConCat extends Facade {
	protected static function getFacadeAccessor(): string {
		return ConCatApiClient::class;
	}
}
