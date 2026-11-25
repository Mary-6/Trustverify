<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Services\SettingsService;
use Illuminate\Http\Request;

class ServiceManagementController extends Controller
{
    public function index(Request $request, SettingsService $settings)
    {
        $categories = Category::whereHas('products')->orderBy('name')->get();
        $suppliers = [
            'manual' => 'Manual',
            'justanotherpanel' => 'JustAnotherPanel',
            'textverified' => 'TextVerified',
            'herosms' => 'HeroSMS',
        ];

        $supplier = (string) $request->query('supplier', '');
        if ($supplier !== '' && ! array_key_exists($supplier, $suppliers)) {
            $supplier = '';
        }
        $state = (string) $request->query('state', 'all');

        $products = Product::with('category')
            ->when($request->filled('q'), fn ($query) => $query->where(fn ($nested) => $nested
                ->where('name', 'like', '%'.$request->string('q').'%')
                ->orWhere('supplier_product_id', 'like', '%'.$request->string('q').'%')
                ->orWhere('summary', 'like', '%'.$request->string('q').'%' )))
            ->when($request->filled('category_id'), fn ($query) => $query->where('category_id', $request->integer('category_id')))
            ->when($supplier !== '', fn ($query) => $query->where(fn ($nested) => $nested
                ->where('supplier_adapter', $supplier)
                ->orWhere('supplier', $supplier)))
            ->when($state === 'active', fn ($query) => $query->where('is_active', true)->where('status', 'active'))
            ->when($state === 'disabled', fn ($query) => $query->where(fn ($nested) => $nested->where('is_active', false)->orWhere('status', '!=', 'active')))
            ->when($state === 'archived', fn ($query) => $query->where('status', 'archived'))
            ->orderByDesc('id')
            ->paginate(50)
            ->withQueryString();

        return view('admin.service-management.index', [
            'products' => $products,
            'categories' => $categories,
            'suppliers' => $suppliers,
            'supplier' => $supplier,
            'serviceLimit' => (int) $settings->get('jap.service_limit', 0),
            'state' => $state,
        ]);
    }

    public function update(Request $request, SettingsService $settings)
    {
        $data = $request->validate([
            'service_limit' => ['nullable', 'integer', 'min:0', 'max:5000'],
            'action' => ['nullable', 'in:activate,deactivate'],
            'selected_ids' => ['nullable', 'array', 'max:500'],
            'selected_ids.*' => ['integer', 'exists:products,id'],
        ]);

        if ($request->has('service_limit')) {
            $settings->set('jap.service_limit', (int) ($data['service_limit'] ?? 0), 'integer', 'supplier');
        }

        $action = $data['action'] ?? null;
        $selected = array_values(array_filter(array_map('intval', (array) ($data['selected_ids'] ?? []))));

        if ($action && $selected === []) {
            return back()->withErrors(['selected_ids' => 'Select at least one service.']);
        }

        if ($action) {
            $activate = $action === 'activate';
            $products = Product::whereIn('id', $selected)->get();

            foreach ($products as $product) {
                $meta = $product->meta ?? [];
                $meta['admin_disabled'] = ! $activate;

                $product->update([
                    'is_active' => $activate,
                    'status' => $activate ? 'active' : 'draft',
                    'meta' => $meta,
                ]);
            }
        }

        return back()->with('status', 'Service settings saved.');
    }
}
