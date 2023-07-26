<?php

namespace App\Providers;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use SocialiteProviders\Manager\OAuth2\User;
use SocialiteProviders\Manager\OAuth2\AbstractProvider as SocialiteProvider;

class ConCatSocialiteProvider extends SocialiteProvider {
	public const IDENTIFIER = 'CONCAT';

	/**
	 * @inheritdoc
	 */
	protected function getAuthUrl($state): string {
		return $this->buildAuthUrlFromBase($this->getInstanceUri() . '/oauth/authorize', $state);
	}

	/**
	 * @inheritdoc
	 */
	protected function getTokenUrl(): string {
		return $this->getInstanceUri() . '/api/oauth/token';
	}

	/**
	 * @inheritdoc
	 */
	protected function getUserByToken($token): array {
		$options = [
			RequestOptions::HEADERS => [
				'Authorization' => "Bearer {$token}",
			],
		];

		// Retrieve the user's details
		$response = $this->getHttpClient()->get("{$this->getInstanceUri()}/api/users/current", $options);
		$user = json_decode((string) $response->getBody(), true);

		// Retrieve the user's registration details (if they have any) to get their badge name
		try {
			$response = $this->getHttpClient()->get("{$this->getInstanceUri()}/api/v0/users/{$user['id']}/registration", $options);
			$registration = json_decode((string) $response->getBody(), true);
			$user['badgeName'] = $registration['badgeName'] ?? null;
		} catch (GuzzleException $_) {
			$user['badgeName'] = null;
		}

		return $user;
	}

	/**
	 * @inheritdoc
	 */
	protected function mapUserToObject(array $user): User {
		return (new User)->setRaw($user)->map([
			'id' => $user['id'],
			'nickname' => $user['username'],
			'name' => "{$user['firstName']} {$user['lastName']}",
			'avatar' => $user['profilePictureUrl'],
		]);
	}

	/**
	 * Gets the ConCat instance URI to use as the base for all OAuth flow requests from the configuration
	 */
	protected function getInstanceUri(): string {
		return $this->getConfig('instance_uri');
	}

	/**
	 * @inheritdoc
	 */
	public static function additionalConfigKeys(): array {
		return ['instance_uri'];
	}
}
