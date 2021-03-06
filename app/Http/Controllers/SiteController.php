<?php

namespace App\Http\Controllers;

use App;
use App\Follower;
use App\Profile;
use App\Status;
use App\User;
use App\UserFilter;
use App\Util\Lexer\PrettyNumber;
use Auth;
use Cache;
use Illuminate\Http\Request;

class SiteController extends Controller
{
    public function home()
    {
        if (Auth::check()) {
            return $this->homeTimeline();
        } else {
            return $this->homeGuest();
        }
    }

    public function homeGuest()
    {
        return view('site.index');
    }

    public function homeTimeline()
    {
        $pid = Auth::user()->profile->id;
        // TODO: Use redis for timelines

        $following = Follower::whereProfileId($pid)->pluck('following_id');
        $following->push($pid)->toArray();

        $filtered = UserFilter::whereUserId($pid)
                    ->whereFilterableType('App\Profile')
                    ->whereIn('filter_type', ['mute', 'block'])
                    ->pluck('filterable_id')->toArray();

        $timeline = Status::whereIn('profile_id', $following)
                  ->whereNotIn('profile_id', $filtered)
                  ->whereHas('media')
                  ->whereVisibility('public')
                  ->orderBy('created_at', 'desc')
                  ->withCount(['comments', 'likes', 'shares'])
                  ->simplePaginate(20);
                  
        $type = 'personal';

        return view('timeline.template', compact('timeline', 'type'));
    }

    public function changeLocale(Request $request, $locale)
    {
        // todo: add other locales after pushing new l10n strings
        $locales = ['en'];
        if(in_array($locale, $locales)) {
          session()->put('locale', $locale);
        }

        return redirect()->back();
    }

    public function about()
    {
        return view('site.about');
    }

    public function language()
    {
      return view('site.language');
    }
}
