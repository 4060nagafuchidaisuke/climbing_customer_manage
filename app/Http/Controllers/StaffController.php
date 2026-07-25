<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStaffRequest;
use App\Http\Requests\UpdateStaffRequest;
use App\Models\Staff;
use App\Services\StaffService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use RuntimeException;

class StaffController extends Controller
{
    public function __construct(
        private StaffService $staffService
    ) {}

    // スタッフ登録画面への遷移
    public function index(): View
    {
        $this->authorize('viewAny', Staff::class);
        $staffs = Staff::orderBy('created_at', 'desc')->paginate(15);

        return view('staffs.index', compact('staffs'));
    }

    // スタッフの登録開始
    public function create(): View
    {
        $this->authorize('create', Staff::class);

        return view('staffs.create');
    }

    // スタッフの登録完了
    public function store(StoreStaffRequest $request): RedirectResponse
    {
        $this->authorize('create', Staff::class);
        $this->staffService->create($request->validated());

        return redirect()->route('staffs.index')->with('success', 'スタッフを登録しました');
    }

    // スタッフの編集画面へ遷移
    public function edit(Staff $staff): View
    {
        $this->authorize('update', $staff);

        return view('staffs.edit', compact('staff'));
    }

    // スタッフの編集後の登録
    public function update(UpdateStaffRequest $request, Staff $staff): RedirectResponse
    {
        $this->authorize('update', $staff);
        try {
            $this->staffService->update($staff, $request->validated(), auth()->user());

            return redirect()->route('staffs.index')->with('success', 'スタッフ情報を更新しました');
        } catch (RuntimeException $e) {
            return redirect()->route('staffs.index')->with('error', $e->getMessage());
        }

    }

    // スタッフの削除
    public function destroy(Staff $staff): RedirectResponse
    {
        $this->authorize('delete', $staff);
        try {
            $this->staffService->delete($staff, auth()->user());
        } catch (RuntimeException $e) {
            return redirect()->route('staffs.index')->with('error', $e->getMessage());
        }

        return redirect()->route('staffs.index')->with('success', 'スタッフを削除しました');
    }

    // 削除スタッフの一覧
    public function trashed(): View
    {
        $this->authorize('viewAny', Staff::class);
        $staffs = Staff::onlyTrashed()->orderBy('deleted_at', 'desc')->paginate(15);

        return view('staffs.trashed', compact('staffs'));
    }

    // 削除スタッフの復活
    public function restore(int $id): RedirectResponse
    {
        $staff = Staff::onlyTrashed()->findOrFail($id);   // 削除済みを探して受け取る
        $this->authorize('update', $staff);               // 認可（探した後に）
        $this->staffService->restore($staff);             // Serviceに委譲

        return redirect()->route('staffs.trashed')->with('success', 'スタッフを復活しました');
    }

    // スタッフの完全削除
    public function forceDelete(int $id): RedirectResponse
    {
        $staff = Staff::onlyTrashed()->findOrFail($id);   // スタッフを探して受け取る
        $this->authorize('delete', $staff);               // 認可（探した後に）
        $this->staffService->forceDelete($staff);             // Serviceに委譲

        return redirect()->route('staffs.trashed')->with('success', 'スタッフを完全に削除しました');
    }
}
