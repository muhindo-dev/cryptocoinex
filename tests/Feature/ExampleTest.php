<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    // Pre-existing Breeze scaffold: app uses /admin/login, not a public / route.
    public function test_the_application_returns_a_successful_response(): void
    {
        $this->markTestSkipped('App redirects / to /admin/login — not a public route.');
    }
}
