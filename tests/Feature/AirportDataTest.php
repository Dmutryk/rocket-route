<?php

namespace Tests\Feature;

use Faker\Factory;
use Faker\Generator;
use Tests\TestCase;

class AirportDataTest extends TestCase
{
    /**
     * @var Generator
     */
    protected $faker;

    public function setup(): void
    {
        parent::setUp();
        $this->faker = Factory::create();
    }

    /**
     * @test
     * @dataProvider icaoProvider
     */
    public function shouldReturnNotasWithWeather($dataProvider)
    {
        $parameters = ['icao' => $dataProvider];
        $response = $this->post('/get-airport-data', $parameters);

        $response->assertStatus(200)
            ->assertJsonStructure(['notam'])
            ->assertJsonStructure(['weather']);
    }

    /**
     * @test
     */
    public function shouldReturnErrorTooShortIcao()
    {
        $parameters = ['icao' => $this->faker->sentence(3)];
        $response = $this->post('/get-airport-data', $parameters);

        $response->assertStatus(422)
            ->assertJsonStructure(['icao']);
    }

    /**
     * @test
     */
    public function shouldReturnErrorTooLongIcao()
    {
        $parameters = ['icao' => $this->faker->sentence(5)];
        $response = $this->post('/get-airport-data', $parameters);

        $response->assertStatus(422)
            ->assertJsonStructure(['icao']);
    }

    /**
     * @test
     */
    public function shouldReturnErrorWithoutIcao()
    {
        $response = $this->post('/get-airport-data');

        $response->assertStatus(422)
            ->assertJsonStructure(['icao']);
    }

    public function icaoProvider()
    {
        return [
            ['EGLL'],
            ['EGGW'],
            ['EGLF'],
            ['EGHI'],
            ['EGKA'],
            ['EGMD'],
            ['EGMC'],
            ['KLAX'],
            ['SBSP'],
        ];
    }
}
