<?php

namespace Tests\Feature;

use Tests\TestCase;

class GoogleMapTest extends TestCase
{
    /**
     * @test
     */
    public function shouldReturnPage()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
