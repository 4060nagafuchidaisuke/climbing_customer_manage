<?php

namespace App\Http\Controllers;

use App\Models\Visit;

class VisitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // 在店中の人
        $activeVisits = Visit::whereNull('check_out_at')->with(['member.activePlan', 'member.previousVisit'])->orderBy('check_in_at', 'asc')->get();

        return view('visits.index', compact('activeVisits'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function checkout(Visit $visit)
    {
        //
        $visit->update(['check_out_at'=>now(),
        ]);

        return redirect()->route('visits.index')->with('success', '退店処理が完了しました');
    }

    // 本日の来店者
    public function today()
   {
       $visits = Visit::whereDate('check_in_at', today())
           ->with(['member.activePlan.plan', 'member.previousVisit'])
           ->orderByDesc('check_in_at')
           ->paginate(10);
       return view('visits.today', compact('visits'));
   }
}