<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserPackage;
use App\Services\CreditService;

class UserManageController extends Controller
{
    protected $creditService;

    public function __construct(CreditService $creditService)
    {
        $this->creditService = $creditService;
    }

    /**
     * Display users list.
     */
    public function index(Request $request)
    {
        $query = User::query();

        // Filter by role
        if ($request->has('role') && $request->role != '') {
            $query->where('role', $request->role);
        }

        // Search by name or email
        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    /**
     * Display user details.
     */
    public function show($id)
    {
        $user = User::with(['bookings.schedule.trainer', 'userPackages.package'])->findOrFail($id);
        $totalCredits = $this->creditService->getUserTotalCredits($id);

        return view('admin.users.show', compact('user', 'totalCredits'));
    }

    /**
     * Show edit user form.
     */
    public function edit($id)
    {
        $user = User::findOrFail($id);

        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update user.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:users,email,' . $id,
            'phone' => 'nullable|string|max:20',
            'role' => 'required|in:user,admin,trainer',
        ]);

        $user = User::findOrFail($id);
        $user->update($request->only(['name', 'email', 'phone', 'role']));

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully');
    }

    /**
     * Approve user package payment.
     */
    public function approvePackage($userId, $packageId)
    {
        $userPackage = UserPackage::where('user_id', $userId)
            ->where('id', $packageId)
            ->firstOrFail();

        $this->creditService->approvePackagePayment($userPackage);

        return redirect()->back()->with('success', 'Package payment approved');
    }

    /**
     * Reject user package payment.
     */
    public function rejectPackage($userId, $packageId)
    {
        $userPackage = UserPackage::where('user_id', $userId)
            ->where('id', $packageId)
            ->firstOrFail();

        $this->creditService->rejectPackagePayment($userPackage);

        return redirect()->back()->with('success', 'Package payment rejected');
    }
}
