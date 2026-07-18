<?php

namespace App\Http\Controllers;

use App\Models\Visit;
use Illuminate\Http\Request;

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
}