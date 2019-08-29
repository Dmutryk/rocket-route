<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Api\LoginInterface;
use App\Http\Controllers\Api\ReLoginInterface;
use Illuminate\Http\Request;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class RocketLoginController extends Controller implements LoginInterface, ReLoginInterface
{
    const ROCKET_ROUTE_TOKEN = 'rocket-route-token';
    const ROCKET_ROUTE_REFRESH_TOKEN = 'rocket-route-refresh-token';
    const LOGIN_URL = "https://flydev.rocketroute.com/api/login";

    /**
     * @var \GuzzleHttp\Client
     */
    private $guzzleHttpClient;

    public function __construct(Client $guzzleHttpClient)
    {
        $this->guzzleHttpClient = $guzzleHttpClient;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Session\SessionManager|\Illuminate\Session\Store|mixed
     */
    public function getToken(Request $request)
    {
        if (!$request->session()->has(self::ROCKET_ROUTE_TOKEN)) {
            $this->login();
        }

        return session(self::ROCKET_ROUTE_TOKEN);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Session\SessionManager|\Illuminate\Session\Store|mixed
     */
    private function getRefreshToken(Request $request)
    {
        if (!$request->session()->has(self::ROCKET_ROUTE_REFRESH_TOKEN)) {
            $this->login();
        }

        return session(self::ROCKET_ROUTE_REFRESH_TOKEN);
    }

    public function login(): void
    {
        try {
            $response = $this->sendLoginRequest($this->getLoginCredentials());

            $body = json_decode($response->getBody()->__toString(), true)['data'];
            $refreshToken = $body['refresh_token'] ?? null;
            $authorizationHeader = $this->getAuthorizationHeader(
                $response->getHeader('Authorization')
            );

        } catch (GuzzleException $exception){
            Log::error($exception->getMessage());
            return;
        }

        $this->saveTokensToSession($authorizationHeader, $refreshToken);
    }

    /**
     * @param Request $request
     */
    public function relogin(Request $request): void
    {
        try {
            $requestBody = json_encode([
                'refresh_token' => $this->getRefreshToken($request),
                'app_key' => env('ROCKET_ROUTE_APP_KEY'),
            ]);
            $response = $this->sendLoginRequest($requestBody);

            $body = json_decode($response->getBody()->__toString(), true)['data'];

            $refreshToken = $body['refresh_token'] ?? null;
            $authorizationHeader = $this->getAuthorizationHeader(
                $response->getHeader('Authorization')
            );

        } catch (GuzzleException $exception){
            Log::error($exception->getMessage());
            return;
        }

        $this->saveTokensToSession($authorizationHeader, $refreshToken);
    }

    /**
     * @param string $body
     * @return mixed|\Psr\Http\Message\ResponseInterface
     * @throws GuzzleException
     */
    private function sendLoginRequest(string $body)
    {
        $response = $this->guzzleHttpClient->request('POST', self::LOGIN_URL, [
            'body' => $body,
            'headers' => ['Content-Type' => 'application/json'],
        ]);

        return $response;
    }
    /**
     * @param array $authorizationHeaderArray
     * @return string
     */
    private function getAuthorizationHeader(array $authorizationHeaderArray): string
    {
        return reset($authorizationHeaderArray);
    }

    /**
     * @param string $header
     * @return string|null
     */
    private function getBearerToken(string $header) {
        if (preg_match('/Bearer\s(\S+)/', $header, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * @return string
     */
    private function getLoginCredentials(): string
    {
        return json_encode([
            'email' => env('ROCKET_ROUTE_EMAIL'),
            'password' => env('ROCKET_ROUTE_PASSWORD'),
            'app_key' => env('ROCKET_ROUTE_APP_KEY'),
        ]);
    }

    /**
     * @param string $authorizationHeader
     * @param string $refreshToken
     */
    private function saveTokensToSession(string $authorizationHeader, string $refreshToken)
    {
        session([self::ROCKET_ROUTE_TOKEN => $this->getBearerToken($authorizationHeader)]);
        session([self::ROCKET_ROUTE_REFRESH_TOKEN => $refreshToken]);
    }
}
