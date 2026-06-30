<?php

namespace App\Http\Controllers;

use App\Services\CheckinService;
use Illuminate\Http\Request;
use App\Models\Notice;
use App\Models\Sponsor;
use RuntimeException;

class CheckinController extends Controller
{
    // Laravel の DI コンテナが自動でインジェクトしてくれる
    public function __construct(private CheckinService $checkinService) {}

    public function index()
    {
        $notices  = Notice::active()->get();
        $sponsors = Sponsor::where('is_active', true)->orderBy('sort_order')->get();

        // Blade に渡す前にコントローラーで整形する
        $sponsorData = $sponsors->map(fn($s) => [
            'url'     => $s->image_url,
            'link'    => $s->link_url,
            'seconds' => $s->display_seconds,
        ]);

        return view('checkin.index', compact('notices', 'sponsors', 'sponsorData'));
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