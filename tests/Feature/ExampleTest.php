<?php

namespace Tests\Feature;

 use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic test example.
     */
    public function test_category_creation(): void
    {
        {
            $payload = [
                'name' => "Testowa nazwa",
                'description' => "opis kategorii",
                'is_active' => true
            ];

            $response = $this->postJson('api/categories', $payload);
            $response->assertStatus(201)
                ->assertJsonStructure([
                    'name',
                    'description',
                    'is_active'
                ]);

            $this->assertDatabaseHas(
                'categories',[
                    'name' => "Testowa nazwa",
                    'description' => "opis kategorii",
                ]
            );
        }
    }
}
