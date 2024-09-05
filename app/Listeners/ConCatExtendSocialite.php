<?php

namespace App\Listeners;

use SocialiteProviders\Manager\SocialiteWasCalled;

class ConCatExtendSocialite {
	public function handle(SocialiteWasCalled $socialiteWasCalled) {
		$socialiteWasCalled->extendSocialite('concat', \App\Providers\ConCatSocialiteProvider::class);
	}
}
