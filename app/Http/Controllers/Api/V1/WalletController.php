<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\WalletService;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    use AuthorizesApiAbilities;

    public function show(Request $request, WalletService $wallets)
    {
        $this->authorizeAbility($request, 'wallet:read');
        $wallet = $wallets->walletFor($request->user());

        return response()->json([
            'data' => [
                'currency' => $wallet->currency,
                'balance' => (float) $wallet->balance,
            ],
        ]);
    }
}
