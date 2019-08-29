<?php

namespace App\Http\Controllers;

use App\Http\Helpers\CoordinateHelper;
use App\Http\Validators\ValidatesIcaoRequests;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class AirportDataController extends Controller
{
    use ValidatesIcaoRequests;

    const NOTAM_URL = 'https://flydev.rocketroute.com/api/data/notam';
    const WEATHER_URL = 'https://flydev.rocketroute.com/api/weather/get';

    /**
     * @var \GuzzleHttp\Client
     */
    private $guzzleHttpClient;

    /**
     * @var \App\Http\Controllers\RocketLoginController
     */
    private $rocketLoginController;

    /**
     * @var CoordinateHelper
     */
    private $coordinateHelper;

    /**
     * @var string
     */
    private $token;

    /**
     * @var Request
     */
    private $request;

    public function __construct(
        Client $guzzleHttpClient,
        RocketLoginController $rocketLoginController,
        CoordinateHelper $coordinateHelper
    )
    {
        $this->guzzleHttpClient = $guzzleHttpClient;
        $this->rocketLoginController = $rocketLoginController;
        $this->coordinateHelper = $coordinateHelper;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAirportData(Request $request)
    {
        $validator = $this->validateIcao($request);

        if ($validator->fails()) {
            throw new HttpResponseException(response()->json($validator->errors(), 422));
        }

        $this->token = $this->rocketLoginController->getToken($request);
        $this->request = $request;
        $icao = $request->get('icao');

        $notam = $this->getNotamData($icao);
        $weather = $this->getWeatherData($icao);

        if (false === $notam || false === $weather) {
            return response()->json(['content' => 'Something wrong. Please, check logs.']);
        }

        return response()->json([
            'notam' => $notam,
            'weather' => $weather,
        ]);
    }

    /**
     * @param string $icao
     * @return bool|false|string
     */
    private function getNotamData(string $icao)
    {
        $markers = false;
        try {
            $response = $this->sendRequest(self::NOTAM_URL, $this->getBody($icao));

            if (isset($response)) {
                $body = $response->getBody()->__toString();
                $notam = json_decode($body, true);
                $status = $notam['status']['success'] ?? false;

                if ($status) {
                    $notamsData = $notam['data'][0]['notams'] ?? [];

                    $markers = $this->coordinateHelper->getCoordinatesFromNotam($notamsData);
                }
            }
        } catch (GuzzleException $exception) {
            Log::error($exception->getMessage());
            return false;
        }

        return json_encode($markers);
    }

    /**
     * @param string $icao
     * @return bool|string
     */
    private function getWeatherData(string $icao)
    {
        $body = false;
        try {
            $response = $this->sendRequest(self::WEATHER_URL, $this->getBody($icao));
            if (isset($response)) {
                $body = $response->getBody()->__toString();
            }
        } catch (GuzzleException $exception) {
            Log::error($exception->getMessage());
            return false;
        }

        return $body;
    }

    /**
     * @param string $url
     * @param string $body
     * @param int $tries
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    private function sendRequest(string $url, string $body, int $tries = 10)
    {
        $response = null;
        try {
            $response = $this->guzzleHttpClient->request('POST', $url, [
                'body' => $body,
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $this->token
                ],
            ]);
        } catch (GuzzleException $exception) {
            if ($tries > 0) {

                Log::error($tries);
                $this->rocketLoginController->relogin($this->request);
                $this->sendRequest($url, $body, $tries - 1);
            }
        }

        return $response;
    }

    /**
     * @param string $icao
     * @return string
     */
    private function getBody(string $icao): string
    {
        return json_encode(['airport' => $icao]);
    }
}
