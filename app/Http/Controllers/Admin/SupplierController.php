<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SupplierOperationsService;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function test(Request $request, string $provider, SupplierOperationsService $operations)
    {
        abort_unless(in_array($provider, ['justanotherpanel', 'textverified', 'herosms'], true), 404);

        $result = $operations->test($provider);

        if ($result['status'] === 'connected') {
            return back()->with('status', $result['message']);
        }

        return back()->withErrors(['supplier' => $result['message']]);
    }

    public function sync(Request $request, string $provider, SupplierOperationsService $operations)
    {
        abort_unless(in_array($provider, ['justanotherpanel', 'textverified', 'herosms'], true), 404);

        $result = $operations->sync($provider);
        $message = $result['error'] ?? $result['message'] ?? 'Supplier sync completed.';

        if (($result['status'] ?? 'failed') === 'success') {
            return back()->with('status', ucfirst($provider).' services synchronized.');
        }

        return back()->withErrors(['supplier' => $message]);
    }
}
