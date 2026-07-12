<?php

namespace App\Policies;

use App\Models\Staff;
use Illuminate\Auth\Access\Response;

class StaffPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    // スタッフ一覧を見られるか
    public function viewAny(Staff $authUser): bool
    {
        return $authUser->role->canManageStaff();
    }

    /**
     * Determine whether the user can create models.
     */
    // スタッフの新規登録が出来るかどうか
    public function create(Staff $authUser): bool
    {
        return $authUser->role->canManageStaff();
    }

    /**
     * Determine whether the user can update the model.
     */
    // スタッフの登録内容を編集できるかどうか
    public function update(Staff $authUser, Staff $staff): bool
    {
        return $authUser->role->canManageStaff();
    }

    /**
     * Determine whether the user can delete the model.
     */
    // スタッフの削除
    public function delete(Staff $authUser, Staff $staff): bool
    {
        return $authUser->role->canManageStaff();
    }

    /**
     * Determine whether the user can delete the model.
     */
    // スタッフの復活
    public function restore(Staff $authUser, Staff $staff): bool
    {
        return $authUser->role->canManageStaff();
    }

    /**
     * Determine whether the user can delete the model.
     */
    // スタッフの完全削除
    public function forceDelete(Staff $authUser, Staff $staff): bool
    {
        return $authUser->role->canManageStaff();
    }

}