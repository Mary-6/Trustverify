<?php

namespace App\Http\Controllers;

use App\Support\ServiceCatalogue;
use App\Support\VirtualNumberCoverage;

class HomeController extends Controller
{
    public function index()
    {
        $rowsByBrand = collect(ServiceCatalogue::brands())
            ->mapWithKeys(fn ($brand, $slug) => [$slug => ServiceCatalogue::catalogueRows($slug)]);

        $descriptions = [
            'instagram' => 'Followers, likes, views, story views and comments for creators, brands and businesses.',
            'tiktok' => 'Video views, likes, shares, comments and followers to reach more people quickly.',
            'facebook' => 'Page likes, followers, post reactions, video views and comments for better reach.',
            'youtube' => 'Views, watch time, subscribers and likes to grow your channel and search ranking.',
            'telegram' => 'Channel and group members, post views and reactions for growing communities.',
            'whatsapp' => 'Channel and group growth services with clear options and tracked delivery.',
        ];

        $brandStats = $rowsByBrand
            ->filter(fn ($rows) => $rows->isNotEmpty())
            ->map(function ($rows, $slug) use ($descriptions) {
                $groups = ServiceCatalogue::groups($slug);
                $serviceExamples = $rows
                    ->pluck('group')
                    ->unique()
                    ->map(fn ($group) => $groups[$group]['label'] ?? str_replace('_', ' ', (string) $group))
                    ->take(4)
                    ->implode(', ');

                return [
                    'slug' => $slug,
                    'name' => ServiceCatalogue::brands()[$slug]['label'],
                    'logo' => ServiceCatalogue::brands()[$slug]['logo'] ?? $slug,
                    'description' => $descriptions[$slug] ?? '',
                    'products_count' => $rows->count(),
                    'service_examples' => $serviceExamples,
                ];
            })
            ->values();

        $featuredProducts = $rowsByBrand
            ->flatten(1)
            ->sortByDesc(fn ($row) => $row['product']->created_at)
            ->take(8)
            ->pluck('product')
            ->values();

        return view('pages.home', [
            'featuredProducts' => $featuredProducts,
            'virtualCountries' => VirtualNumberCoverage::featured(),
            'brandStats' => $brandStats,
        ]);
    }
}
