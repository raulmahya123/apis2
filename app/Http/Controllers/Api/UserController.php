<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $query = User::query();

        if ($request->role) {
            $query->where('role', $request->role);
        }

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%")
                  ->orWhere('nomor_induk', 'like', "%{$request->search}%");
            });
        }

        $users = $query->paginate($request->per_page ?? 15);

        return $this->success(UserResource::collection($users));
    }

    public function show(User $user): JsonResponse
    {
        return $this->success(new UserResource($user));
    }

    public function dosenList(): JsonResponse
    {
        $dosen = User::whereIn('role', ['dosen_pembimbing', 'penguji'])->get();
        return $this->success(UserResource::collection($dosen));
    }

    public function mahasiswaList(): JsonResponse
    {
        $mahasiswa = User::where('role', 'mahasiswa')->get();
        return $this->success(UserResource::collection($mahasiswa));
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'jurusan' => 'sometimes|string|max:255',
            'angkatan' => 'sometimes|string|max:20',
            'no_telepon' => 'sometimes|string|max:20',
        ]);

        $user->update($request->only(['name', 'jurusan', 'angkatan', 'no_telepon']));

        return $this->success(new UserResource($user->fresh()), 'User berhasil diperbarui');
    }

    public function destroy(User $user): JsonResponse
    {
        $user->delete();
        return $this->success(null, 'User berhasil dihapus');
    }
}
