<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_root_returns_api_metadata(): void
    {
        $response = $this->getJson('/');

        $response->assertOk()
            ->assertJsonPath('name', 'PHP Users REST API')
            ->assertJsonStructure(['docs', 'schema', 'health']);
    }
}
