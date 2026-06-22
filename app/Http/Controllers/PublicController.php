<?php

namespace App\Http\Controllers;

use App\Models\Education\EducationArticle;
use App\Models\Trading\Asset;
use App\Models\Trading\TradingSetting;

class PublicController extends Controller
{
    /**
     * Landing page. Logged-in users go straight to their workspace.
     */
    public function home()
    {
        if (auth()->check()) {
            return auth()->user()->canAccessAdmin()
                ? redirect()->route('admin.dashboard')
                : redirect()->route('trade.index');
        }

        return view('public.home', $this->shared());
    }

    public function features()
    {
        return view('public.features', $this->shared());
    }

    public function how()
    {
        return view('public.how', $this->shared());
    }

    public function academy()
    {
        return view('public.academy', $this->shared());
    }

    public function faq()
    {
        return view('public.faq', $this->shared());
    }

    public function contact()
    {
        return view('public.contact', $this->shared());
    }

    /**
     * Shared marketing data, driven entirely by the admin trading settings so
     * copy and figures stay in sync with the live configuration.
     */
    private function shared(): array
    {
        $currency = TradingSetting::get('live_account_currency', 'USD');
        $minDeposit = (float) TradingSetting::get('live_account_min_deposit', 0);
        $maxPayout = (int) (Asset::where('enabled', true)->max('payout_percent') ?: 80);
        $symbol = $currency === 'USD' ? '$' : $currency.' ';

        return [
            'assetCount'   => Asset::where('enabled', true)->count() ?: 13,
            'lessonCount'  => EducationArticle::count() ?: 42,
            'startBalance' => (int) TradingSetting::get('default_start_balance', 10000),
            'currency'     => $currency,
            'minDeposit'   => $minDeposit,
            'maxPayout'    => $maxPayout,
            'cur'          => $currency,
            'payout'       => $maxPayout,
            'minLabel'     => $minDeposit > 0 ? $symbol.number_format($minDeposit) : 'No minimum',
            'fundFrom'     => $minDeposit > 0 ? 'from '.$symbol.number_format($minDeposit) : 'with any amount',
        ];
    }
}
