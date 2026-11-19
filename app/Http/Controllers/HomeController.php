<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Support\ServiceCatalogue;

class HomeController extends Controller
{
    public function index()
    {
        $rowsByBrand = collect(ServiceCatalogue::brands())
            ->mapWithKeys(fn ($brand, $slug) => [$slug => ServiceCatalogue::catalogueRows($slug)]);

        $brandStats = $rowsByBrand
            ->filter(fn ($rows) => $rows->isNotEmpty())
            ->map(fn ($rows, $slug) => [
                'slug' => $slug,
                'name' => ServiceCatalogue::brands()[$slug]['label'],
                'products_count' => $rows->count(),
            ])
            ->values();

        $featuredProducts = $rowsByBrand
            ->flatten(1)
            ->sortByDesc(fn ($row) => $row['product']->created_at)
            ->take(8)
            ->pluck('product')
            ->values();

        return view('pages.home', [
            'featuredProducts' => $featuredProducts,
            'brandStats' => $brandStats,
            'stats' => [
                'smm_products' => $rowsByBrand->flatten(1)->count(),
                'brands' => $brandStats->count(),
                'virtual_numbers' => Product::active()->where('type', 'virtual_number')->count(),
            ],
        ]);
    }
}
