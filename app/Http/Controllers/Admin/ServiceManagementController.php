<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Services\SettingsService;
use App\Support\ServiceCatalogue;
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

        $displayLimits = $settings->get('smm.display_limits', []);

        return view('admin.service-management.index', [
            'products' => $products,
            'categories' => $categories,
            'suppliers' => $suppliers,
            'supplier' => $supplier,
            'serviceLimit' => (int) $settings->get('jap.service_limit', 0),
            'state' => $state,
            'smmBrands' => ServiceCatalogue::brands(),
            'displayLimits' => is_array($displayLimits) ? $displayLimits : [],
        ]);
    }

    public function update(Request $request, SettingsService $settings)
    {
        $data = $request->validate([
            'service_limit' => ['nullable', 'integer', 'min:0', 'max:5000'],
            'action' => ['nullable', 'in:activate,deactivate'],
            'selected_ids' => ['nullable', 'array', 'max:500'],
            'selected_ids.*' => ['integer', 'exists:products,id'],
            'display_limits' => ['nullable', 'array'],
            'display_limits.*.*' => ['nullable', 'integer', 'min:0', 'max:500'],
        ]);

        if ($request->has('service_limit')) {
            $settings->set('jap.service_limit', (int) ($data['service_limit'] ?? 0), 'integer', 'supplier');
        }

        if ($request->has('display_limits')) {
            $clean = [];
            foreach ((array) $request->input('display_limits', []) as $brand => $groups) {
                if (! isset(ServiceCatalogue::brands()[$brand]) || ! is_array($groups)) {
                    continue;
                }
                foreach ($groups as $group => $limit) {
                    if (isset(ServiceCatalogue::groups($brand)[$group]) && is_numeric($limit) && (int) $limit > 0) {
                        $clean[$brand][$group] = (int) $limit;
                    }
                }
            }
            $settings->set('smm.display_limits', $clean, 'json', 'catalogue');
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
