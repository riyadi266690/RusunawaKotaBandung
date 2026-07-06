<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class BssnApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'email' => 'admin@test.com',
            'password' => Hash::make('password123')
        ]);
    }

    /**
     * Test BSSN endpoints require authentication.
     */
    public function test_bssn_endpoints_require_authentication(): void
    {
        $endpoints = [
            '/api/bssn/seal',
            '/api/bssn/unseal',
            '/api/bssn/seal-names',
            '/api/bssn/unseal-names',
            '/api/bssn/hmac',
            '/api/bssn/verify-hmac',
        ];

        foreach ($endpoints as $endpoint) {
            $response = $this->postJson($endpoint);
            $response->assertStatus(401);
        }
    }

    /**
     * Test validation for seal endpoint.
     */
    public function test_seal_validation(): void
    {
        $response = $this->withHeaders([
            'PHP_AUTH_USER' => 'admin@test.com',
            'PHP_AUTH_PW' => 'password123',
        ])->postJson('/api/bssn/seal', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['text']);
    }

    /**
     * Test validation for unseal endpoint.
     */
    public function test_unseal_validation(): void
    {
        $response = $this->withHeaders([
            'PHP_AUTH_USER' => 'admin@test.com',
            'PHP_AUTH_PW' => 'password123',
        ])->postJson('/api/bssn/unseal', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['ciphertext']);
    }

    /**
     * Test validation for seal-names endpoint.
     */
    public function test_seal_names_validation(): void
    {
        $response = $this->withHeaders([
            'PHP_AUTH_USER' => 'admin@test.com',
            'PHP_AUTH_PW' => 'password123',
        ])->postJson('/api/bssn/seal-names', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['plaintexts']);
    }

    /**
     * Test validation for unseal-names endpoint.
     */
    public function test_unseal_names_validation(): void
    {
        $response = $this->withHeaders([
            'PHP_AUTH_USER' => 'admin@test.com',
            'PHP_AUTH_PW' => 'password123',
        ])->postJson('/api/bssn/unseal-names', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['ciphertexts']);
    }

    /**
     * Test validation for hmac endpoint.
     */
    public function test_hmac_validation(): void
    {
        $response = $this->withHeaders([
            'PHP_AUTH_USER' => 'admin@test.com',
            'PHP_AUTH_PW' => 'password123',
        ])->postJson('/api/bssn/hmac', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['text']);
    }

    /**
     * Test validation for verify-hmac endpoint.
     */
    public function test_verify_hmac_validation(): void
    {
        $response = $this->withHeaders([
            'PHP_AUTH_USER' => 'admin@test.com',
            'PHP_AUTH_PW' => 'password123',
        ])->postJson('/api/bssn/verify-hmac', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['text', 'hmac']);
    }
}
