<?php

namespace App\Http\Controllers;

use App\LoanType;
use App\Models\FaqItem;
use App\NodCredit\Settings;
use Illuminate\Http\Request;

class FrontendController extends Controller
{

    /**
     * Home page
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getHome()
    {
        $faqItems = FaqItem::where('category', 'loan')
            ->where('is_active', true)
            ->orderBy('sort', 'ASC')
            ->get();

        return view('frontend-v2.index', [
            'faqItems' => $faqItems
        ]);
    }

    /**
     * Invest page
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getInvest()
    {
        $faqItems = FaqItem::where('category', 'invest')
            ->where('is_active', true)
            ->orderBy('sort', 'ASC')
            ->get();

        return view('frontend-v2.invest', [
            'faqItems' => $faqItems
        ]);
    }

    /**
     * Invest start page
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getInvestStart(Request $request)
    {
        return view('frontend-v2.invest-start');
    }

    /**
     * Invest start info
     * @param Settings $settings
     * @return \Illuminate\Http\JsonResponse
     */
    public function getInvestInfo(Settings $settings)
    {
        return response()->json([
            'plans' => json_decode($settings->get('investmentConfig')),
            'min_amount' => $settings->get('investment_min_amount', 10000),
            'max_amount' => $settings->get('investment_max_amount', 1000000)
        ]);
    }

    /**
     * Terms page
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getTerms()
    {
        return view('frontend-v2.terms');
    }

    /**
     * Policy page
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getPolicy()
    {
        return view('frontend-v2.policy');
    }

    /**
     * Loan start page
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getLoanStart(Request $request)
    {
        return view('frontend-v2.loan-start');
    }

    /**
     * Loan start info
     * @return \Illuminate\Http\JsonResponse
     */
    public function getLoanInfo()
    {
        return response()->json([
            'loanTypes' => LoanType::orderBy('name')->get()
        ]);
    }

}
