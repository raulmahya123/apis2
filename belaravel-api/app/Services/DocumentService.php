<?php

namespace App\Services;

use App\Models\DocumentVersion;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class DocumentService
{
    public function upload(UploadedFile $file, string $path = 'documents'): array
    {
        $originalName = $file->getClientOriginalName();
        $fileSize = $file->getSize();
        $fileType = $file->getMimeType();
        $storedPath = $file->store($path, 'public');

        return [
            'file_path' => $storedPath,
            'original_name' => $originalName,
            'file_size' => $fileSize,
            'file_type' => $fileType,
        ];
    }

    public function createVersion($documentable, UploadedFile $file, int $uploaderId, string $path = 'documents', ?string $keterangan = null): DocumentVersion
    {
        $fileInfo = $this->upload($file, $path);

        $latestVersion = DocumentVersion::where('documentable_type', get_class($documentable))
            ->where('documentable_id', $documentable->id)
            ->max('version_number') ?? 0;

        return DocumentVersion::create([
            'documentable_type' => get_class($documentable),
            'documentable_id' => $documentable->id,
            'uploader_id' => $uploaderId,
            'file_path' => $fileInfo['file_path'],
            'original_name' => $fileInfo['original_name'],
            'version_number' => $latestVersion + 1,
            'file_size' => $fileInfo['file_size'],
            'file_type' => $fileInfo['file_type'],
            'keterangan' => $keterangan,
        ]);
    }

    public function getVersions($documentable)
    {
        return DocumentVersion::where('documentable_type', get_class($documentable))
            ->where('documentable_id', $documentable->id)
            ->orderBy('version_number', 'desc')
            ->get();
    }

    public function deleteFile(string $path): bool
    {
        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->delete($path);
        }
        return false;
    }

    public function validateFile(UploadedFile $file, array $allowedTypes = [], int $maxSize = 20480): bool|string
    {
        if ($allowedTypes && !in_array($file->getMimeType(), $allowedTypes)) {
            return 'Tipe file tidak didukung';
        }

        if ($file->getSize() > $maxSize * 1024) {
            return "Ukuran file maksimal {$maxSize} KB";
        }

        return true;
    }
}
