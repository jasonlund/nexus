<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected $sampleHTML =
        "<p>Hello World</p>\n" .
        "<h1><s>Test</s> <u>diff<strong>erent</strong></u> <em>Styles</em></h1>\n\n" .
        "<blockquote>Foobar</blockquote>\n"
    ;

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
