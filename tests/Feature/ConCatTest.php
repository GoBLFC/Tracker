<?php

use App\Providers\ConCatSocialiteProvider;
use App\Services\ConCat\ConCatApiClient;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request as GuzzleRequest;
use GuzzleHttp\Psr7\Response;
use SocialiteProviders\Manager\Config;

/**
 * Builds a Guzzle client that answers with queued responses and records the requests made to it
 *
 * @param array<Response|Throwable> $responses
 * @param array $history Filled with the requests made
 */
function mockHttpClient(array $responses, array &$history, array $options = []): Client {
	$stack = HandlerStack::create(new MockHandler($responses));
	$stack->push(Middleware::history($history));
	return new Client(['handler' => $stack, ...$options]);
}

/**
 * Creates a ConCat API client whose requests are answered with queued responses
 */
function mockConCatClient(array $responses, array &$history): ConCatApiClient {
	$client = new ConCatApiClient(['instance_uri' => 'https://concat.test', 'client_id' => 'id', 'client_secret' => 'secret']);
	$httpClient = mockHttpClient($responses, $history, ['base_uri' => 'https://concat.test']);
	(fn () => $this->httpClient = $httpClient)->call($client);
	return $client;
}

/**
 * Builds a JSON response
 */
function jsonResponse(mixed $data, int $status = 200): Response {
	return new Response($status, ['Content-Type' => 'application/json'], json_encode($data));
}

it('starts out unauthorized', function () {
	$history = [];
	expect(mockConCatClient([], $history)->isAuthorized())->toBeFalse();
});

it('retrieves single entities', function () {
	$history = [];
	$client = mockConCatClient([
		jsonResponse(['id' => 1234, 'username' => 'foxy']),
		jsonResponse(['badgeName' => 'Foxy']),
		jsonResponse(['id' => 5]),
	], $history);

	expect($client->getUser(1234)->username)->toBe('foxy')
		->and($client->getRegistration(1234)->badgeName)->toBe('Foxy')
		->and($client->getVolunteer(1234)->id)->toBe(5)
		->and(array_map(fn ($entry) => (string) $entry['request']->getUri(), $history))->toBe([
			'https://concat.test/api/v0/users/1234',
			'https://concat.test/api/v0/users/1234/registration',
			'https://concat.test/api/v0/users/1234/volunteer',
		]);
});

it('follows pagination to retrieve every result', function () {
	$history = [];
	$client = mockConCatClient([
		jsonResponse(['data' => [['id' => 1], ['id' => 2]], 'hasMore' => true, 'nextPage' => 'page-2']),
		jsonResponse(['data' => [['id' => 3]], 'hasMore' => false]),
	], $history);

	$results = $client->searchVolunteers();

	expect(array_column($results, 'id'))->toBe([1, 2, 3])
		->and((string) $history[0]['request']->getBody())->toBe('{}')
		->and(json_decode($history[1]['request']->getBody(), true))->toBe(['nextPage' => 'page-2']);
});

it('searches registrations by user IDs in chunks of 100', function () {
	$history = [];
	$client = mockConCatClient([
		jsonResponse(['data' => [['id' => 'a']], 'hasMore' => false]),
		jsonResponse(['data' => [['id' => 'b']], 'hasMore' => false]),
	], $history);

	$results = $client->searchRegistrationsByUserIds(array_map('strval', range(1, 150)));

	$filters = array_map(fn ($entry) => json_decode($entry['request']->getBody(), true)['filter']['userIds'], $history);
	expect(array_column($results, 'id'))->toBe(['a', 'b'])
		->and(count($filters[0]))->toBe(100)
		->and(count($filters[1]))->toBe(50);
});

it('passes on client errors when not authorized', function () {
	$history = [];
	$client = mockConCatClient([new Response(404)], $history);

	$client->getUser(1234);
})->throws(GuzzleHttp\Exception\ClientException::class);

/**
 * Creates the ConCat Socialite provider with an HTTP client answering with queued responses
 */
function mockConCatSocialite(array $responses, array &$history): ConCatSocialiteProvider {
	$provider = new ConCatSocialiteProvider(request(), 'id', 'secret', 'https://tracker.test/auth/callback');
	$provider->setConfig(new Config('id', 'secret', 'https://tracker.test/auth/callback', ['instance_uri' => 'https://concat.test']));
	$provider->setHttpClient(mockHttpClient($responses, $history));
	return $provider;
}

it('builds the ConCat authorization URL', function () {
	$history = [];
	$url = mockConCatSocialite([], $history)->stateless()->redirect()->getTargetUrl();

	expect($url)->toStartWith('https://concat.test/oauth/authorize?')
		->toContain('client_id=id')
		->toContain('redirect_uri=' . urlencode('https://tracker.test/auth/callback'));
});

it('retrieves the OAuth user along with their badge name', function () {
	$history = [];
	$provider = mockConCatSocialite([
		jsonResponse(['id' => 1234, 'username' => 'foxy', 'firstName' => 'Fox', 'lastName' => 'Face', 'profilePictureUrl' => null]),
		jsonResponse(['badgeName' => 'Foxy']),
	], $history);

	$user = $provider->userFromToken('oauth-token');

	expect($user->getId())->toBe(1234)
		->and($user->getNickname())->toBe('foxy')
		->and($user->getName())->toBe('Fox Face')
		->and($user->user['badgeName'])->toBe('Foxy')
		->and($history[0]['request']->getHeaderLine('Authorization'))->toBe('Bearer oauth-token')
		->and((string) $history[1]['request']->getUri())->toBe('https://concat.test/api/v0/users/1234/registration');
});

it('retrieves OAuth users without a registration', function () {
	$history = [];
	$provider = mockConCatSocialite([
		jsonResponse(['id' => 1234, 'username' => 'foxy', 'firstName' => 'Fox', 'lastName' => 'Face', 'profilePictureUrl' => null]),
		new GuzzleHttp\Exception\ClientException('Not Found', new GuzzleRequest('GET', '/'), new Response(404)),
	], $history);

	expect($provider->userFromToken('oauth-token')->user['badgeName'])->toBeNull();
});
