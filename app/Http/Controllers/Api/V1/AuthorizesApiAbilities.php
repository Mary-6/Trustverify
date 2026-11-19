<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;

trait AuthorizesApiAbilities
{
    protected function authorizeAbility(Request $request, string $ability): void
    {
        $key = $request->attributes->get('api_key');

        abort_unless(
            $key && in_array($ability, $key->abilities ?? [], true),
            403,
            'API token does not include the required ability.'
        );
    }
}
