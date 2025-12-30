<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Package;

class PackageManageController extends Controller
{
    /**
     * Display packages list.
     */
    public function index()
    {
        $packages = Package::orderBy('price', 'asc')->paginate(20);

        return view('admin.packages.index', compact('packages'));
    }

    /**
     * Show create package form.
     */
    public function create()
    {
        return view('admin.packages.create');
    }

    /**
     * Store new package.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'total_credits' => 'required|integer|min:1',
            'days_valid' => 'required|integer|min:1',
            'is_active' => 'required|boolean',
        ]);

        Package::create($request->all());

        return redirect()->route('admin.packages.index')->with('success', 'Package created successfully');
    }

    /**
     * Show edit package form.
     */
    public function edit($id)
    {
        $package = Package::findOrFail($id);

        return view('admin.packages.edit', compact('package'));
    }

    /**
     * Update package.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'total_credits' => 'required|integer|min:1',
            'days_valid' => 'required|integer|min:1',
            'is_active' => 'required|boolean',
        ]);

        $package = Package::findOrFail($id);
        $package->update($request->all());

        return redirect()->route('admin.packages.index')->with('success', 'Package updated successfully');
    }

    /**
     * Delete package.
     */
    public function destroy($id)
    {
        $package = Package::findOrFail($id);
        
        if ($package->userPackages()->count() > 0) {
            return redirect()->back()->with('error', 'Cannot delete package with existing purchases');
        }

        $package->delete();

        return redirect()->route('admin.packages.index')->with('success', 'Package deleted successfully');
    }
}
