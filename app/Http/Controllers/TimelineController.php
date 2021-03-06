<?php

namespace App\Http\Controllers;

use Auth, Cache;
use App\Follower;
use App\Profile;
use App\Status;
use App\User;
use App\UserFilter;
use Illuminate\Http\Request;

class TimelineController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('twofactor');
    }

    public function local(Request $request)
    {
        $this->validate($request,[
          'page' => 'nullable|integer|max:20'
        ]);
        // TODO: Use redis for timelines
        // $timeline = Timeline::build()->local();
        $pid = Auth::user()->profile->id;

        $private = Profile::whereIsPrivate(true)->where('id', '!=', $pid)->pluck('id');
        $filters = UserFilter::whereUserId($pid)
                  ->whereFilterableType('App\Profile')
                  ->whereIn('filter_type', ['mute', 'block'])
                  ->pluck('filterable_id')->toArray();
        $filtered = array_merge($private->toArray(), $filters);

        $timeline = Status::whereHas('media')
                  ->whereNotIn('profile_id', $filtered)
                  ->whereNull('in_reply_to_id')
                  ->whereNull('reblog_of_id')
                  ->whereVisibility('public')
                  ->withCount(['comments', 'likes'])
                  ->orderBy('created_at', 'desc')
                  ->simplePaginate(10);
        $type = 'local';

        return view('timeline.template', compact('timeline', 'type'));
    }
}
