<?php

namespace App\Http\Controllers;

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
     * @var string
     */
    private $token;

    public function __construct(Client $guzzleHttpClient, RocketLoginController $rocketLoginController)
    {
        $this->guzzleHttpClient = $guzzleHttpClient;
        $this->rocketLoginController = $rocketLoginController;
    }

    public function getAirportData(Request $request)
    {
        $validator = $this->validateIcao($request);

        if ($validator->fails()) {
            throw new HttpResponseException(response()->json($validator->errors(), 422));
        }

        $this->token = $this->rocketLoginController->getToken($request);
        $icao = $request->get('icao');

        $notam = $this->getNotamData($icao);
        $weather = $this->getWeatherData($icao);

        return response()->json([
            'notam' => $notam,
            'weather' => $weather,
        ]);
    }

    private function getWeatherData(string $icao)
    {
        try {
            $response = $this->guzzleHttpClient->request('POST', self::WEATHER_URL,  [
                'body' => $this->getBody($icao),
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $this->token
                ],
            ]);

            $body = $response->getBody()->__toString();
        } catch (GuzzleException $exception){
            Log::error($exception->getMessage());
            return;
        }

        return $body;
    }

    private function getNotamData(string $icao)
    {
        try {
            $response = $this->guzzleHttpClient->request('POST', self::NOTAM_URL,  [
                'body' => $this->getBody($icao),
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $this->token
                ],
            ]);

            $body = $response->getBody()->__toString();
        } catch (GuzzleException $exception){
            Log::error($exception->getMessage());
            return;
        }

        return $body;
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
