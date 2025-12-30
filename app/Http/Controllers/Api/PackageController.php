<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Package;
use App\Services\CreditService;

class PackageController extends Controller
{
    protected $creditService;

    public function __construct(CreditService $creditService)
    {
        $this->creditService = $creditService;
    }

    /**
     * Get all active packages.
     */
    public function index()
    {
        $packages = Package::where('is_active', true)
            ->orderBy('price', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $packages,
        ]);
    }

    /**
     * Get user's credit balance.
     */
    public function credits(Request $request)
    {
        $user = $request->user();
        
        $totalCredits = $this->creditService->getUserTotalCredits($user->id);
        $activePackages = $this->creditService->getUserActivePackages($user->id);

        return response()->json([
            'success' => true,
            'data' => [
                'total_credits' => $totalCredits,
                'packages' => $activePackages,
            ],
        ]);
    }

    /**
     * Purchase a package.
     */
    public function purchase(Request $request)
    {
        $request->validate([
            'package_id' => 'required|exists:packages,id',
            'payment_slip' => 'required|image|max:5120', // 5MB
        ]);

        $user = $request->user();
        $paymentSlipPath = null;

        // Handle payment slip upload
        if ($request->hasFile('payment_slip')) {
            $paymentSlipPath = $request->file('payment_slip')->store('package-slips', 'public');
        }

        try {
            $userPackage = $this->creditService->purchasePackage(
                $user->id,
                $request->package_id,
                $paymentSlipPath
            );

            return response()->json([
                'success' => true,
                'message' => 'Package purchase request submitted. Waiting for approval.',
                'data' => $userPackage->load('package'),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
