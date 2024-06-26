<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Spatie\Analytics\Facades\Analytics;
use Spatie\Analytics\Period;
class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
       $this->middleware('auth:admin');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $startDate = Carbon::createFromDate(2021, 6, 1);
        $endDate = Carbon::now();
        $error = null;
        try{
        $data = Analytics::fetchTotalVisitorsAndPageViews(Period::days(7));
        $toprefer = Analytics::fetchTopReferrers(Period::days(7));
        $topbrow = Analytics::fetchTopBrowsers(Period::days(7));
        $topbrowpercent = $topbrow->map(function($item) use ($topbrow){return ['percent'=>round($item['screenPageViews']*100/$topbrow->sum('screenPageViews'), 2), 'browser'=>$item['browser']];});
        //dd(Analytics::performQuery(Period::days(7), 'ga:users, ga:sessionsPerUser', ['metrics' => 'ga:pageviews, ga:users','dimensions' => 'ga:browser']));
        //dd(ga:users);
		$totalvisitor = Analytics::get(Period::create($startDate, $endDate), ['totalUsers'], ['year'])->sum('totalUsers');    
        }catch(\Exception $e){
            $data=collect();
            $toprefer = collect();  
            $topbrowpercent = collect();
            $totalvisitor = null;
            $error = 'Không liên kết được google analytic';
        }
        return view('Admin.home', compact('data', 'toprefer', 'topbrowpercent', 'totalvisitor', 'error'));
    }
    public function viewfile()
    {
        $ext = strtolower(substr(request()->fn, -3));
        return view('Admin.view_picture', compact('ext'));
    }    
}