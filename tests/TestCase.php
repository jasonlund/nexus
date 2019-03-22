<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected $sampleMarkdown =
        "*Italic*\n" .
        "**Bold**\n\n" .
        "# Heading 1\n" .
        "## Heading 2\n\n" .
        "[Link](http://a.com)\n" .
        "![Image](http://url/a.png)\n" .
        "> Blockquote\n\n" .
        "* List\n* List\n* List\n" .
        "1. List\n2. List\n 3. List\n" .
        "Horizontal Rule\n\n---" .
        "`Inline code` with backticks\n\n" .
        "```\n# code block\nprint '3 backticks or'\nprint 'indent 4 spaces'\n```"
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
