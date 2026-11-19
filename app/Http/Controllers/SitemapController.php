<?php

namespace App\Http\Controllers;

use App\Models\Product;

class SitemapController extends Controller
{
    public function index()
    {
        $staticPages = [
            ['loc' => route('home'), 'priority' => '1.0', 'changefreq' => 'daily'],
            ['loc' => route('services.index'), 'priority' => '0.9', 'changefreq' => 'daily'],
            ['loc' => route('pricing'), 'priority' => '0.7', 'changefreq' => 'weekly'],
            ['loc' => route('api.docs'), 'priority' => '0.6', 'changefreq' => 'monthly'],
            ['loc' => route('support'), 'priority' => '0.6', 'changefreq' => 'monthly'],
        ];

        $products = Product::active()
            ->latest('updated_at')
            ->get(['id', 'updated_at'])
            ->map(fn (Product $product) => [
                'loc' => route('services.show', $product),
                'lastmod' => $product->updated_at?->toDateString(),
                'priority' => '0.8',
                'changefreq' => 'weekly',
            ]);

        return response()
            ->view('sitemap', ['pages' => collect($staticPages)->merge($products)])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
