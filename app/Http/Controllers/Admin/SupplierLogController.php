<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupplierLog;
use Illuminate\Http\Request;

class SupplierLogController extends Controller
{
    public function index(Request $request)
    {
        $logs = SupplierLog::with('order')
            ->when($request->filled('supplier'), fn ($q) => $q->where('supplier', $request->string('supplier')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->latest()
            ->paginate(30)
            ->withQueryString();

        return view('admin.supplier-logs.index', compact('logs'));
    }
}
