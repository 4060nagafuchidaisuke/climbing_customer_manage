<?php

namespace App\Http\Controllers;

use App\Models\Notice;
use App\Models\Sponsor;
use App\Services\CheckinService;
use Illuminate\Http\Request;
use RuntimeException;

class CheckinController extends Controller
{
    // Laravel の DI コンテナが自動でインジェクトしてくれる
    public function __construct(private CheckinService $checkinService) {}

    public function index()
    {
        $notices = Notice::active()->get();
        $sponsors = Sponsor::where('is_active', true)->orderBy('sort_order')->get();

        return view('checkin.index', compact('notices', 'sponsors'));
    }

    public function process(Request $request)
    {
        $request->validate([
            'barcode' => 'required|string|max:20',
        ]);

        try {
            $result = $this->checkinService->process(trim($request->barcode));

            return back()->with($result);
        } catch (RuntimeException $e) {
            return back()->with('checkin_error', $e->getMessage());
        }
    }
}
