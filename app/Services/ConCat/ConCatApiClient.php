<?php

namespace App\Services\ConCat;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

/**
 * @mixin \GuzzleHttp\ClientInterface
 * @mixin \Psr\Http\Client\ClientInterface
 */
class ConCatApiClient {
	protected Client $httpClient;
	protected ?string $accessToken = null;

	public function __construct(protected array $config) {
		$this->httpClient = $this->buildHttpClient();
	}

	public function __call(string $name, array $arguments): mixed {
		try {
			// Call the method on the HTTP client
			return $this->httpClient->$name(...$arguments);
		} catch (ClientException $err) {
			// If we get a 401 error and we're supposed to be authorized, go ahead and reauthorize then try again
			if ($err->getCode() === 401 && $this->isAuthorized()) {
				$this->authorize();
				return $this->httpClient->$name(...$arguments);
			}

			throw $err;
		}
	}

	/**
	 * Obtains authorization from the API
	 */
	public function authorize(): void {
		$response = $this->buildHttpClient(false)->post('/api/oauth/token', [
			'form_params' => [
				'client_id' => $this->config['client_id'],
				'client_secret' => $this->config['client_secret'],
				'grant_type' => 'client_credentials',
				'scope' => 'volunteer:read registration:read',
			],
		]);

		$this->accessToken = json_decode($response->getBody())->access_token;
		$this->httpClient = $this->buildHttpClient();
	}

	/**
	 * Checks whether authorization has already been obtained
	 */
	public function isAuthorized(): bool {
		return $this->accessToken !== null;
	}

	/**
	 * Sends enoguh requests necessary to pull all paginated results from a resource
	 */
	public function requestAllPaginated(string $method, string $uri, array $body = []): array {
		$results = [];
		$pageData = null;

		do {
			// Prepare the body for this request
			$reqBody = $body;
			if ($pageData) $reqBody['nextPage'] = $pageData->nextPage;

			// Make the request - we do some shenanigans between the JSON/body elements because Guzzle won't
			// encode an empty array as an empty JSON object
			$response = $this->request($method, $uri, [
				'headers' => ['Content-Type' => 'application/json'],
				'json' => count($reqBody) > 0 ? $reqBody : null,
				'body' => count($reqBody) === 0 ? '{}' : null,
			]);

			// Decode and add the data from this page
			$pageData = json_decode($response->getBody());
			array_push($results, ...$pageData->data);
		} while ($pageData->hasMore);

		return $results;
	}

	/**
	 * Retrieves volunteers matching search criteria
	 */
	public function searchVolunteers(array $body = []): array {
		return $this->requestAllPaginated('POST', '/api/v0/volunteers/search', $body);
	}

	/**
	 * Retrieves a single registration by user ID
	 */
	public function getRegistration(int $userId): \stdClass {
		$response = $this->get("/api/v0/users/{$userId}/registration", [
			'headers' => ['Accept' => 'application/json'],
		]);
		return json_decode($response->getBody());
	}

	/**
	 * Retrieves registrations matching search criteria
	 */
	public function searchRegistrations(array $body = []): array {
		return $this->requestAllPaginated('POST', '/api/v0/registration/search', $body);
	}

	/**
	 * Builds a new HTTP client
	 */
	private function buildHttpClient(bool $authorized = true): Client {
		$options = [
			'base_uri' => $this->config['instance_uri'],
			'timeout' => 5,
			'headers' => [],
		];
		if ($authorized && $this->isAuthorized()) $options['headers']['Authorization'] = "Bearer {$this->accessToken}";
		return new Client($options);
	}
}
