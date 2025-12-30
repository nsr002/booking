<?php

namespace App\Services;

use App\Models\UserPackage;
use App\Models\Package;
use Illuminate\Support\Facades\DB;

class CreditService
{
    /**
     * Purchase a package for user.
     */
    public function purchasePackage(int $userId, int $packageId, ?string $paymentSlipPath = null)
    {
        return DB::transaction(function () use ($userId, $packageId, $paymentSlipPath) {
            $package = Package::findOrFail($packageId);

            if (!$package->is_active) {
                throw new \Exception('This package is not available.');
            }

            $userPackage = UserPackage::create([
                'user_id' => $userId,
                'package_id' => $packageId,
                'credits_remaining' => $package->total_credits,
                'purchased_at' => now(),
                'expires_at' => now()->addDays($package->days_valid),
                'payment_slip_path' => $paymentSlipPath,
                'payment_status' => 'pending',
            ]);

            return $userPackage;
        });
    }

    /**
     * Approve package payment.
     */
    public function approvePackagePayment(UserPackage $userPackage)
    {
        $userPackage->update([
            'payment_status' => 'approved',
        ]);

        return $userPackage;
    }

    /**
     * Reject package payment.
     */
    public function rejectPackagePayment(UserPackage $userPackage)
    {
        $userPackage->update([
            'payment_status' => 'rejected',
        ]);

        return $userPackage;
    }

    /**
     * Get user's active packages.
     */
    public function getUserActivePackages(int $userId)
    {
        return UserPackage::where('user_id', $userId)
            ->where('payment_status', 'approved')
            ->where('expires_at', '>', now())
            ->where('credits_remaining', '>', 0)
            ->with('package')
            ->get();
    }

    /**
     * Get user's total available credits.
     */
    public function getUserTotalCredits(int $userId): int
    {
        return UserPackage::where('user_id', $userId)
            ->where('payment_status', 'approved')
            ->where('expires_at', '>', now())
            ->sum('credits_remaining');
    }

    /**
     * Deduct credit from user's package.
     */
    public function deductCredit(UserPackage $userPackage)
    {
        if (!$userPackage->hasCredits()) {
            throw new \Exception('No credits remaining.');
        }

        $userPackage->decrement('credits_remaining');

        return $userPackage;
    }

    /**
     * Refund credit to user's package.
     */
    public function refundCredit(UserPackage $userPackage)
    {
        $userPackage->increment('credits_remaining');

        return $userPackage;
    }
}
