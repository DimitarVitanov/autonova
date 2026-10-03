<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class PricingController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Pricing', [
            'packages' => config('marketplace.packages'),
            'promotions' => config('marketplace.promotions'),
        ]);
    }
}
