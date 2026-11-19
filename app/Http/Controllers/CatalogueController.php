<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Support\ServiceCatalogue;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class CatalogueController extends Controller
{
    public function index(Request $request)
    {
        $serviceTypes = [
            'smm_service' => 'SMM Services',
            'virtual_number' => 'Virtual Numbers',
        ];

        $requestedService = $request->filled('service')
            ? (string) $request->input('service')
            : ($request->filled('type') ? (string) $request->input('type') : 'smm_service');

        $selectedService = array_key_exists($requestedService, $serviceTypes) ? $requestedService : 'smm_service';

        $serviceOptions = [];
        foreach ($serviceTypes as $slug => $label) {
            $serviceOptions[] = [
                'slug' => $slug,
                'label' => $label,
                'available' => Product::active()->where('type', $slug)->exists(),
            ];
        }

        $selectedOption = collect($serviceOptions)->firstWhere('slug', $selectedService);
        $selectedServiceLabel = $selectedOption['label'] ?? 'SMM Services';

        if ($selectedService === 'smm_service') {
            return $this->smmIndex($request, $serviceOptions, $selectedService, $selectedServiceLabel);
        }

        return $this->catalogIndex($request, $serviceTypes, $serviceOptions, $selectedService, $selectedServiceLabel);
    }

    public function show(Product $product)
    {
        abort_unless($product->is_active && $product->status === 'active', 404);

        return view('pages.services.show', [
            'product' => $product->load('category'),
            'related' => Product::active()->where('id', '!=', $product->id)
                ->where('type', $product->type)
                ->latest('id')
                ->limit(6)
                ->get(),
        ]);
    }

    private function smmIndex(Request $request, array $serviceOptions, string $selectedService, string $selectedServiceLabel)
    {
        $brandSummaries = [];
        foreach (ServiceCatalogue::brands() as $slug => $brand) {
            $brandSummaries[$slug] = ServiceCatalogue::summaries($slug);
        }

        $brandOptions = [];
        foreach (ServiceCatalogue::brands() as $slug => $brand) {
            $summaries = $brandSummaries[$slug];
            $productCount = array_sum(array_column($summaries, 'count'));
            $brandOptions[] = [
                'slug' => $slug,
                'label' => $brand['label'],
                'available' => $productCount > 0,
                'product_count' => $productCount,
            ];
        }

        $selectedBrandSlug = $request->filled('brand') ? (string) $request->input('brand') : null;

        if ($selectedBrandSlug !== null && ! isset(ServiceCatalogue::brands()[$selectedBrandSlug])) {
            $selectedBrandSlug = null;
        }

        if ($selectedBrandSlug === null && $request->filled('category_id')) {
            $category = Category::find($request->integer('category_id'));
            if ($category && isset(ServiceCatalogue::brands()[$category->slug])) {
                $selectedBrandSlug = $category->slug;
            }
        }

        $selectedBrand = $selectedBrandSlug
            ? collect($brandOptions)->firstWhere('slug', $selectedBrandSlug)
            : null;
        $awaitingBrand = $selectedBrand === null;
        $brandComingSoon = $selectedBrand !== null && ! $selectedBrand['available'];

        $selectedGroupSlug = null;
        $selectedGroup = null;
        $groupComingSoon = false;
        $products = null;

        if ($selectedBrand && $selectedBrand['available']) {
            $summaries = $brandSummaries[$selectedBrandSlug];
            $requestedGroup = $request->filled('group') ? (string) $request->input('group') : null;

            if ($requestedGroup !== null && isset($summaries[$requestedGroup])) {
                $selectedGroupSlug = $requestedGroup;
                $selectedGroup = $summaries[$requestedGroup];
                $groupComingSoon = ! $selectedGroup['available'];

                if (! $groupComingSoon) {
                    $items = ServiceCatalogue::productsForGroup($selectedBrandSlug, $selectedGroupSlug);

                    if ($request->filled('q')) {
                        $term = Str::lower($request->string('q')->toString());
                        $items = $items->filter(function (Product $product) use ($term) {
                            return Str::contains(Str::lower($product->name.' '.($product->summary ?? '')), $term);
                        })->values();
                    }

                    $page = max(1, $request->integer('page', 1));
                    $perPage = 24;
                    $products = new LengthAwarePaginator(
                        $items->forPage($page, $perPage)->values(),
                        $items->count(),
                        $perPage,
                        $page,
                        ['path' => route('services.index'), 'query' => $request->query()]
                    );
                }
            }
        }

        return view('pages.services.index', [
            'products' => $products,
            'categories' => collect(),
            'serviceOptions' => $serviceOptions,
            'selectedService' => $selectedService,
            'selectedServiceLabel' => $selectedServiceLabel,
            'comingSoon' => false,
            'brandOptions' => $brandOptions,
            'selectedBrand' => $selectedBrand,
            'selectedBrandSlug' => $selectedBrandSlug,
            'awaitingBrand' => $awaitingBrand,
            'brandComingSoon' => $brandComingSoon,
            'brandSummaries' => $selectedBrand ? ($brandSummaries[$selectedBrandSlug] ?? []) : [],
            'selectedGroup' => $selectedGroup,
            'selectedGroupSlug' => $selectedGroupSlug,
            'groupComingSoon' => $groupComingSoon,
        ]);
    }

    private function catalogIndex(Request $request, array $serviceTypes, array $serviceOptions, string $selectedService, string $selectedServiceLabel)
    {
        $query = Product::active()->with('category')->latest('id')->where('type', $selectedService);

        if ($request->filled('q')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->string('q').'%')
                    ->orWhere('summary', 'like', '%'.$request->string('q').'%');
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->integer('category_id'));
        }

        $products = $query->paginate(24)->withQueryString();
        $categories = Category::active()
            ->whereHas('products', function ($q) use ($selectedService) {
                $q->where('is_active', true)->where('status', 'active')->where('type', $selectedService);
            })
            ->get();

        return view('pages.services.index', [
            'products' => $products,
            'categories' => $categories,
            'serviceOptions' => $serviceOptions,
            'selectedService' => $selectedService,
            'selectedServiceLabel' => $selectedServiceLabel,
            'comingSoon' => $products->total() === 0,
            'brandOptions' => [],
            'selectedBrand' => null,
            'selectedBrandSlug' => null,
            'awaitingBrand' => false,
            'brandComingSoon' => false,
            'brandSummaries' => [],
            'selectedGroup' => null,
            'selectedGroupSlug' => null,
            'groupComingSoon' => false,
        ]);
    }
}
