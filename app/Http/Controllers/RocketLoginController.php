<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class RocketLoginController extends Controller
{
    const ROCKET_ROUTE_TOKEN = 'rocket-route-token';
    const LOGIN_URL = "https://flydev.rocketroute.com/api/login";

    /**
     * @var \GuzzleHttp\Client
     */
    private $guzzleHttpClient;

    public function __construct(Client $guzzleHttpClient)
    {
        $this->guzzleHttpClient = $guzzleHttpClient;
    }

    public function getToken(Request $request)
    {
        //if (!$request->session()->has(self::ROCKET_ROUTE_TOKEN)) {
            $this->login();
        //}

        return session(self::ROCKET_ROUTE_TOKEN);
    }

    public function login(): void
    {
        try {
            $response = $this->guzzleHttpClient->request('POST', self::LOGIN_URL,  [
                'body' => $this->getLoginCredentials(),
                'headers' => ['Content-Type' => 'application/json'],
            ]);

            $authorizationHeader = $this->getAuthorizationHeader($response->getHeader('Authorization'));
        } catch (GuzzleException $exception){
            Log::error($exception->getMessage());
            return;
        }

        session([self::ROCKET_ROUTE_TOKEN => $this->getBearerToken($authorizationHeader)]);
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
}
