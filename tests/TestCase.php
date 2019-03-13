<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    public function setUp()
    {
        parent::setUp();

        $this->withoutExceptionHandling();
    }

    protected function apiAs($user, $method, $uri, array $data = [], array $headers = [])
    {
        $headers = array_merge([
            'Authorization' => 'Bearer ' . \JWTAuth::fromUser($user),
        ], $headers);

        return $this->json($method, $uri, $data, $headers);
    }
}
