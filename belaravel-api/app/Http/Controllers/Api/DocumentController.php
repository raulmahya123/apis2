<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DocumentVersionResource;
use App\Models\DocumentVersion;
use App\Services\DocumentService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    use ApiResponse;

    protected DocumentService $documentService;

    public function __construct(DocumentService $documentService)
    {
        $this->documentService = $documentService;
    }

    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png,zip|max:20480',
            'documentable_type' => 'required|string',
            'documentable_id' => 'required|integer',
            'keterangan' => 'nullable|string',
        ]);

        $documentableType = $request->documentable_type;
        $documentable = $documentableType::findOrFail($request->documentable_id);

        $version = $this->documentService->createVersion(
            $documentable,
            $request->file('file'),
            $request->user()->id,
            'documents',
            $request->keterangan
        );

        return $this->created(new DocumentVersionResource($version), 'Dokumen berhasil diupload');
    }

    public function versions(Request $request): JsonResponse
    {
        $request->validate([
            'documentable_type' => 'required|string',
            'documentable_id' => 'required|integer',
        ]);

        $documentableType = $request->documentable_type;
        $documentable = $documentableType::findOrFail($request->documentable_id);

        $versions = $this->documentService->getVersions($documentable);

        return $this->success(DocumentVersionResource::collection($versions));
    }

    public function show(DocumentVersion $documentVersion): JsonResponse
    {
        $documentVersion->load('uploader');
        return $this->success(new DocumentVersionResource($documentVersion));
    }

    public function destroy(DocumentVersion $documentVersion): JsonResponse
    {
        $this->documentService->deleteFile($documentVersion->file_path);
        $documentVersion->delete();

        return $this->success(null, 'Dokumen berhasil dihapus');
    }
}
