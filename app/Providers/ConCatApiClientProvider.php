<?php

namespace App\Providers;

use App\Services\ConCat\ConCatApiClient;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class ConCatApiClientProvider extends ServiceProvider implements DeferrableProvider {
	public function register(): void {
		$this->app->singleton(ConCatApiClient::class, function (Application $_app) {
			return new ConCatApiClient(config('services.concat'));
		});
	}

	public function provides(): array {
		return [ConCatApiClient::class];
	}
}
