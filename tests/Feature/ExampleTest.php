<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        // Корень ведёт в админку, а гостя — на вход.
        $this->get('/')->assertRedirect('/che168');
        $this->get('/che168/login')->assertOk();
        $this->get('/up')->assertOk();
    }
}
