<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected $sampleHTML = "<p>Hello World</p>\n" .
        "<p><s>Test</s> <u>diff<strong>erent</strong></u> <em>Styles</em></p>\n" .
        "<blockquote>Foobar</blockquote>\n" .
        "<p><img src=\"http://www.google.com/image.jpg\" alt=\"image.jpg\"></p>";

    protected $nullHTML = '<p><strong> </strong><em><s> </s> </em></p><p> <u> </u></p> <p></p>';

    public function setUp(): void
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
