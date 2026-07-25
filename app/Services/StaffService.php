<?php

namespace App\Services;

use App\Enums\StaffRole;
use App\Models\Staff;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class StaffService
{
    /**
     * スタッフを新規登録する
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Staff
    {
        return Staff::create([
            'name' => $data['name'],
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
            'email' => $data['email'] ?? null,
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
            'is_active' => $data['is_active'] ?? true,
        ]);
    }

    /**
     * スタッフ情報を更新する
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Staff $staff, array $data, Staff $currentUser): Staff
    {
        // 降格しようとした場合の防御
        if ($staff->id === $currentUser->id && $data['role'] !== StaffRole::ADMIN->value) {
            throw new RuntimeException('自分自身を降格させることはできません');
        }

        // 最後の管理者を防御
        if ($staff->role === StaffRole::ADMIN) {
            $adminCount = Staff::where('role', StaffRole::ADMIN->value)->count();
            if ($adminCount === 1) {
                throw new RuntimeException('最後の管理者は降格できません。');
            }
        }

        // 無効化を防御
        if ($staff->id === $currentUser->id && $staff->is_active && ! $data['is_active']) {
            throw new RuntimeException('管理者が自分自身を無効かすることはできません');
        }

        // 最後の管理者の無効化を防御
        if ($staff->role === StaffRole::ADMIN && $staff->is_active && ! $data['is_active']) {
            $adminCount = Staff::where('role', StaffRole::ADMIN->value)->count();
            if ($adminCount === 1) {
                throw new RuntimeException('最後の管理者を無効化することはできません。');
            }
        }

        $staff->fill([
            'name' => $data['name'],
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
            'email' => $data['email'] ?? null,
            'role' => $data['role'],
            'is_active' => $data['is_active'] ?? false,
        ]);

        //
        if (! empty($data['password'])) {
            $staff->password = Hash::make($data['password']);
        }

        $staff->save();

        return $staff;
    }

    /**
     * スタッフを削除する
     *
     * @throws RuntimeException 自分自身または最後の管理者は削除不可
     */
    public function delete(Staff $staff, Staff $currentUser): void
    {
        if ($staff->id === $currentUser->id) {
            throw new RuntimeException('自分自身のアカウントは削除できません。');
        }

        if ($staff->role === StaffRole::ADMIN) {
            $adminCount = Staff::where('role', StaffRole::ADMIN->value)->count();
            if ($adminCount <= 1) {
                throw new RuntimeException('最後の管理者は削除できません。');
            }
        }

        $staff->delete();
    }

    // 削除したスタッフを復活させる
    public function restore(Staff $staff): void
    {
        $staff->restore();
    }

    // 完全に削除する
    public function forceDelete(Staff $staff): void
    {
        $staff->forceDelete();
    }
}
