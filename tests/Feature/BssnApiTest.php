<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BssnApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test validation for seal endpoint.
     */
    public function test_seal_validation(): void
    {
        $response = $this->postJson('/api/bssn/seal', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['text']);
    }

    /**
     * Test validation for unseal endpoint.
     */
    public function test_unseal_validation(): void
    {
        $response = $this->postJson('/api/bssn/unseal', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['ciphertext']);
    }

    /**
     * Test validation for seal-names endpoint.
     */
    public function test_seal_names_validation(): void
    {
        $response = $this->postJson('/api/bssn/seal-names', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['plaintexts']);
    }

    /**
     * Test validation for unseal-names endpoint.
     */
    public function test_unseal_names_validation(): void
    {
        $response = $this->postJson('/api/bssn/unseal-names', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['ciphertexts']);
    }

    /**
     * Test validation for hmac endpoint.
     */
    public function test_hmac_validation(): void
    {
        $response = $this->postJson('/api/bssn/hmac', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['text']);
    }

    /**
     * Test validation for verify-hmac endpoint.
     */
    public function test_verify_hmac_validation(): void
    {
        $response = $this->postJson('/api/bssn/verify-hmac', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['text', 'hmac']);
    }
}
