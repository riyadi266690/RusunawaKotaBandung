<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BssnApiController extends Controller
{
    /**
     * Seal (encrypt) a single text.
     *
     * Encrypts a single plaintext string using the BSSN seal service.
     */
    public function seal(Request $request)
    {
        $validated = $request->validate([
            'text' => 'required|string',
        ]);

        $result = seal($validated['text']);

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    /**
     * Unseal (decrypt) a single ciphertext.
     *
     * Decrypts a single ciphertext string using the BSSN unseal service.
     */
    public function unseal(Request $request)
    {
        $validated = $request->validate([
            'ciphertext' => 'required|string',
        ]);

        $decryptedMap = unseal([$validated['ciphertext']]);
        $result = $decryptedMap[$validated['ciphertext']] ?? null;

        return response()->json([
            'success' => true,
            'data' => [
                'plaintext' => $result
            ],
        ]);
    }

    /**
     * Seal (encrypt) multiple names.
     *
     * Encrypts an array of plaintexts in batch using the BSSN sealNames service.
     */
    public function sealNames(Request $request)
    {
        $validated = $request->validate([
            'plaintexts' => 'required|array',
            'plaintexts.*' => 'required|string',
        ]);

        $result = sealNames($validated['plaintexts']);

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    /**
     * Unseal (decrypt) multiple names.
     *
     * Decrypts an array of ciphertexts in batch using the BSSN unsealNames service.
     */
    public function unsealNames(Request $request)
    {
        $validated = $request->validate([
            'ciphertexts' => 'required|array',
            'ciphertexts.*' => 'required|string',
        ]);

        $result = unsealNames($validated['ciphertexts']);

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    /**
     * Generate HMAC.
     *
     * Generates a HMAC hash for a single plaintext string.
     */
    public function hmac(Request $request)
    {
        $validated = $request->validate([
            'text' => 'required|string',
        ]);

        $result = hmac($validated['text']);

        return response()->json([
            'success' => true,
            'data' => [
                'hmac' => $result,
            ],
        ]);
    }

    /**
     * Verify HMAC.
     *
     * Verifies if a given HMAC matches the plaintext string.
     */
    public function verifyHmac(Request $request)
    {
        $validated = $request->validate([
            'text' => 'required|string',
            'hmac' => 'required|string',
        ]);

        $result = verifyhmac($validated['text'], $validated['hmac']);

        return response()->json([
            'success' => true,
            'data' => [
                'valid' => $result,
            ],
        ]);
    }
}
