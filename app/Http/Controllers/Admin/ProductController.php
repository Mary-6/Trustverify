<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::with('category')
            ->when($request->filled('q'), fn ($q) => $q->where('name', 'like', '%'.$request->string('q').'%'))
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->string('type')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('admin.products.index', compact('products'));
    }

    public function create()
    {
        return view('admin.products.form', [
            'product' => new Product(),
            'categories' => Category::active()->get(),
        ]);
    }

    public function store(Request $request)
    {
        Product::create($this->validated($request));

        return redirect()->route('admin.products.index')->with('status', 'Product created.');
    }

    public function edit(Product $product)
    {
        return view('admin.products.form', [
            'product' => $product,
            'categories' => Category::active()->get(),
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $product->update($this->validated($request, $product));

        return redirect()->route('admin.products.index')->with('status', 'Product updated.');
    }

    public function destroy(Product $product)
    {
        $product->update(['is_active' => false, 'status' => 'archived']);

        return back()->with('status', 'Product archived.');
    }

    private function validated(Request $request, ?Product $product = null): array
    {
        $data = $request->validate([
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:160'],
            'type' => ['required', 'in:smm_service,virtual_number'],
            'summary' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string', 'max:20000'],
            'terms' => ['nullable', 'string', 'max:10000'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'size:3'],
            'pricing_unit' => ['nullable', 'in:per_unit,per_1000'],
            'supplier_cost' => ['nullable', 'numeric', 'min:0'],
            'supplier' => ['nullable', 'string', 'max:40'],
            'supplier_product_id' => ['nullable', 'string', 'max:120'],
            'supplier_adapter' => ['nullable', 'in:faussms,manual,textverified,herosms,justanotherpanel'],
            'supplier_service_name' => ['nullable', 'string', 'max:160'],
            'supplier_capability' => ['nullable', 'string', 'max:50'],
            'supplier_area_codes' => ['nullable', 'string', 'max:500'],
            'supplier_carriers' => ['nullable', 'string', 'max:500'],
            'supplier_service_not_listed_name' => ['nullable', 'string', 'max:160'],
            'supplier_max_price' => ['nullable', 'numeric', 'min:0'],
            'supplier_country_id' => ['nullable', 'string', 'max:40'],
            'status' => ['required', 'in:draft,active,inactive,archived'],
            'is_active' => ['boolean'],
            'is_featured' => ['boolean'],
            'sort_order' => ['nullable', 'integer'],
            'stock_quantity' => ['nullable', 'integer', 'min:0'],
            'min_order' => ['nullable', 'integer', 'min:1'],
            'max_order' => ['nullable', 'integer', 'min:1'],
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $data['is_featured'] = $request->boolean('is_featured');

        $meta = $product?->meta ?? [];
        foreach (['service_name', 'capability', 'area_codes', 'carriers', 'service_not_listed_name', 'max_price', 'country_id', 'pricing_unit'] as $key) {
            unset($meta[$key]);
        }

        $serviceName = trim((string) $request->input('supplier_service_name', ''));
        if ($serviceName !== '') {
            $meta['service_name'] = $serviceName;
        }

        $capability = trim((string) $request->input('supplier_capability', ''));
        if ($capability !== '') {
            $meta['capability'] = $capability;
        }

        $areaCodes = $this->csvToList($request->input('supplier_area_codes'));
        if ($areaCodes) {
            $meta['area_codes'] = $areaCodes;
        }

        $carriers = $this->csvToList($request->input('supplier_carriers'));
        if ($carriers) {
            $meta['carriers'] = $carriers;
        }

        $serviceNotListed = trim((string) $request->input('supplier_service_not_listed_name', ''));
        if ($serviceNotListed !== '') {
            $meta['service_not_listed_name'] = $serviceNotListed;
        }

        if ($request->filled('supplier_max_price')) {
            $meta['max_price'] = (float) $request->input('supplier_max_price');
        }

        $countryId = trim((string) $request->input('supplier_country_id', ''));
        if ($countryId !== '') {
            $meta['country_id'] = $countryId;
        }

        $pricingUnit = trim((string) $request->input('pricing_unit', ''));
        if ($pricingUnit !== '') {
            $meta['pricing_unit'] = $pricingUnit;
        }

        $data['meta'] = $meta ?: null;

        return $data;
    }

    private function csvToList(mixed $value): array
    {
        if (is_array($value)) {
            $value = implode(',', $value);
        }

        return array_values(array_filter(array_map('trim', preg_split('/[\s,]+/', (string) $value) ?: [])));
    }
}
