<?php

namespace Tests\Feature;

use App\Models\MainDomain;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        MainDomain::factory()->create(['domain' => config('tenancy.main_domain')]);

        $response = $this->get('http://'.config('tenancy.main_domain').'/');

        $response->assertStatus(200);
    }
}
