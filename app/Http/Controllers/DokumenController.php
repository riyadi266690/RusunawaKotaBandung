<?php

namespace App\Http\Controllers;

use App\Http\Requests\Dokumen\UploadRevisiRequest;
use App\Http\Requests\Dokumen\SignDocumentRequest;
use App\Models\Kontrak;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\TemplateProcessor;

class DokumenController extends Controller
{
    /**
     * Upload File Revisi (PDF/DOCX)
     */
    public function uploadRevisi(UploadRevisiRequest $request, $id)
    {
        try {
            $kontrak = Kontrak::findOrFail($id);

            $file = $request->file('file_revisi');
            $extension = $file->getClientOriginalExtension();
            $fileName = $kontrak->id . '_revisi_' . time() . '.' . $extension;
            $folderPath = 'kontrak/' . $kontrak->id;

            $path = $file->storeAs($folderPath, $fileName, 'public');

            $kontrak->update([
                'dok_kontrak' => $path,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Dokumen revisi berhasil diupload!',
                'path' => $path
            ]);
        } catch (\Exception $e) {
            Log::error("Upload Revisi Error: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal upload: ' . $e->getMessage()], 500);
        }
    }

    public function signDocument(SignDocumentRequest $request, $id)
    {
        try {
            $kontrak = Kontrak::findOrFail($id);

            $relativePath = $kontrak->dok_kontrak;
            $fullPath = storage_path('app/public/' . $relativePath);

            if (!file_exists($fullPath)) {
                return response()->json(['success' => false, 'message' => 'Dokumen fisik tidak ditemukan.'], 404);
            }

            $image_parts = explode(";base64,", $request->signature_image);
            $image_base64 = base64_decode($image_parts[1]);

            $tempDir = storage_path('app/public/temp');
            if (!file_exists($tempDir)) mkdir($tempDir, 0777, true);

            $tempImageName = 'temp_ttd_' . $kontrak->id . '_' . time() . '.png';
            $tempImagePath = $tempDir . '/' . $tempImageName;

            file_put_contents($tempImagePath, $image_base64);

            $templateProcessor = new TemplateProcessor($fullPath);

            $templateProcessor->setImageValue('ttd_penghuni', [
                'path' => $tempImagePath,
                'width' => 200,
                'height' => 200,
                'ratio' => true
            ]);

            $tempOutputPath = $tempDir . '/output_' . $kontrak->id . '_' . time() . '.docx';
            $templateProcessor->saveAs($tempOutputPath);

            if (file_exists($tempOutputPath)) {
                if (file_exists($fullPath)) unlink($fullPath);
                rename($tempOutputPath, $fullPath);
            }

            if (file_exists($tempImagePath)) unlink($tempImagePath);
            $kontrak->update(['status_ttd' => 2]);
            return response()->json([
                'success' => true,
                'message' => 'Dokumen berhasil ditandatangani secara digital!'
            ]);
        } catch (\Exception $e) {
            Log::error("Sign Document Error: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal tanda tangan: ' . $e->getMessage()], 500);
        }
    }
}
