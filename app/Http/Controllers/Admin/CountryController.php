<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CountryController extends Controller
{
    public function index()
    {
        return view('admin.countries.index', [
            'countries' => Country::orderBy('sort_order')->orderBy('name')->paginate(25),
        ]);
    }

    public function create()
    {
        return view('admin.countries.form', ['country' => new Country()]);
    }

    public function store(Request $request)
    {
        Country::create($this->validated($request));

        return redirect()->route('admin.countries.index')->with('status', 'Country created.');
    }

    public function edit(Country $country)
    {
        return view('admin.countries.form', compact('country'));
    }

    public function update(Request $request, Country $country)
    {
        $country->update($this->validated($request, $country));

        return redirect()->route('admin.countries.index')->with('status', 'Country updated.');
    }

    public function destroy(Country $country)
    {
        $country->delete();

        return back()->with('status', 'Country deleted.');
    }

    private function validated(Request $request, ?Country $country = null): array
    {
        $codeRule = $country
            ? Rule::unique('countries', 'code')->ignore($country->id)
            : Rule::unique('countries', 'code');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'code' => ['required', 'string', 'size:2', $codeRule],
            'dial_code' => ['nullable', 'string', 'max:10'],
            'flag' => ['nullable', 'string', 'max:10'],
            'currency' => ['nullable', 'string', 'size:3'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ]);

        $data['code'] = strtoupper($data['code']);
        $data['currency'] = isset($data['currency']) ? strtoupper($data['currency']) : null;
        $data['is_active'] = $request->boolean('is_active', true);

        return $data;
    }
}
