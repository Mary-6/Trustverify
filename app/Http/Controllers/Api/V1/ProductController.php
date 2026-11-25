<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    use AuthorizesApiAbilities;

    public function index(Request $request)
    {
        $this->authorizeAbility($request, 'products:read');

        $products = Product::active()->with('category')
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->string('type')))
            ->when($request->filled('q'), fn ($q) => $q->where('name', 'like', '%'.$request->string('q').'%'))
            ->latest('id')
            ->paginate(min((int) $request->integer('per_page', 25), 100));

        return response()->json([
            'data' => $products->getCollection()
                ->reject(fn (Product $product) => $product->isCountrySpecificSmm())
                ->values()
                ->map(fn (Product $product) => $this->present($product)),
            'meta' => ['current_page' => $products->currentPage(), 'last_page' => $products->lastPage(), 'total' => $products->total()],
        ]);
    }

    public function show(Request $request, Product $product)
    {
        $this->authorizeAbility($request, 'products:read');
        abort_unless($product->isActiveForPurchase(), 404);

        return response()->json(['data' => $this->present($product->load('category'), true)]);
    }

    private function present(Product $product, bool $detail = false): array
    {
        $data = [
            'id' => $product->id,
            'name' => $product->name,
            'type' => $product->type,
            'category' => $product->category?->name,
            'summary' => $product->summary,
            'price' => (float) $product->price,
            'currency' => $product->currency,
            'pricing_unit' => $product->pricingUnit(),
            'min_order' => $product->min_order,
            'max_order' => $product->max_order,
            'available' => $product->isAvailable(),
        ];

        if ($detail) {
            $data['description'] = $product->customerDescription();
            $data['terms'] = $product->customerTerms();
        }

        return $data;
    }
}
