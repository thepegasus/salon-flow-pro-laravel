<?php

namespace App\Services;

use App\Models\StaffProfile;
use App\Models\User;
use App\Repositories\Contracts\StaffProfileRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class StaffService
{
    public function __construct(
        private StaffProfileRepositoryInterface $staffProfileRepository,
        private TenantContext $tenantContext,
    ) {}

    /** @param array<string, mixed> $data */
    public function create(array $data): StaffProfile
    {
        $tenant = $this->tenantContext->get();

        return DB::transaction(function () use ($data, $tenant): StaffProfile {
            $user = User::create([
                'tenant_id' => $tenant->id,
                'name' => $data['name'],
                'username' => $data['username'],
                'email' => $data['email'] ?? null,
                'password' => Hash::make($data['password']),
            ]);

            $user->assignRole($data['role']);

            $staffProfile = $this->staffProfileRepository->create([
                'tenant_id' => $tenant->id,
                'user_id' => $user->id,
                'job_title' => $data['job_title'],
                'phone' => $data['phone'] ?? null,
                'photo_path' => $data['photo_path'] ?? null,
                'is_active' => $data['is_active'] ?? true,
            ]);

            if (! empty($data['service_ids'])) {
                $staffProfile->services()->sync($data['service_ids']);
            }

            return $staffProfile;
        });
    }

    /** @param array<string, mixed> $data */
    public function update(StaffProfile $staffProfile, array $data): StaffProfile
    {
        return DB::transaction(function () use ($staffProfile, $data): StaffProfile {
            $this->staffProfileRepository->update($staffProfile, [
                'job_title' => $data['job_title'] ?? $staffProfile->job_title,
                'phone' => $data['phone'] ?? $staffProfile->phone,
                'photo_path' => $data['photo_path'] ?? $staffProfile->photo_path,
                'is_active' => $data['is_active'] ?? $staffProfile->is_active,
            ]);

            if (array_key_exists('service_ids', $data)) {
                $staffProfile->services()->sync($data['service_ids']);
            }

            if (! empty($data['role'])) {
                $staffProfile->user->syncRoles([$data['role']]);
            }

            return $staffProfile->refresh();
        });
    }

    public function deactivate(StaffProfile $staffProfile): StaffProfile
    {
        return $this->staffProfileRepository->update($staffProfile, ['is_active' => false]);
    }
}
