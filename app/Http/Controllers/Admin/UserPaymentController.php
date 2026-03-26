<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UserPaymentController extends Controller
{
    /**
     * Display a listing of the user's payments.
     */
    public function index(User $user)
    {
        $payments = $user->payments()
            ->latest()
            ->paginate(10);

        // Calculate stats for the stats cards
        $stats = [
            'total_payments' => $payments->total(),
            'total_amount' => $user->payments()->sum('amount'),
            'pending_payments' => $user->payments()->where('status', 'pending')->count(),
        ];

        return view('admin.users.payments.index', compact('user', 'payments', 'stats'));
    }

    /**
     * Show the form for creating a new payment.
     */
    public function create(User $user)
    {
        return view('admin.users.payments.create', compact('user'));
    }

    /**
     * Store a newly created payment in storage.
     */
    public function store(Request $request, User $user)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|max:255',
            'payment_method' => 'required|string|in:credit_card,paypal,bank_transfer,other',
            'status' => 'required|string|in:completed,pending,failed,refunded',
            'notes' => 'nullable|string',
        ]);

        $payment = DB::transaction(function () use ($user, $validated) {
            return $user->payments()->create([
                'transaction_id' => 'PAY-' . strtoupper(Str::random(10)),
                'amount' => $validated['amount'],
                'currency' => 'USD', // Default currency, can be made dynamic
                'description' => $validated['description'],
                'payment_method' => $validated['payment_method'],
                'status' => $validated['status'],
                'notes' => $validated['notes'] ?? null,
            ]);
        });

        return redirect()
            ->route('admin.users.payments.show', [$user, $payment])
            ->with('success', 'Payment created successfully');
    }

    /**
     * Display the specified payment.
     */
    public function show(User $user, Payment $payment)
    {
        // Ensure the payment belongs to the user
        if ($payment->user_id !== $user->id) {
            abort(404);
        }

        return view('admin.users.payments.show', compact('user', 'payment'));
    }

    /**
     * Show the form for editing the specified payment.
     */
    public function edit(User $user, Payment $payment)
    {
        // Ensure the payment belongs to the user
        if ($payment->user_id !== $user->id) {
            abort(404);
        }

        return view('admin.users.payments.edit', compact('user', 'payment'));
    }

    /**
     * Update the specified payment in storage.
     */
    public function update(Request $request, User $user, Payment $payment)
    {
        // Ensure the payment belongs to the user
        if ($payment->user_id !== $user->id) {
            abort(404);
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|max:255',
            'payment_method' => 'required|string|in:credit_card,paypal,bank_transfer,other',
            'status' => 'required|string|in:completed,pending,failed,refunded',
            'notes' => 'nullable|string',
        ]);

        $payment->update($validated);

        return redirect()
            ->route('admin.users.payments.show', [$user, $payment])
            ->with('success', 'Payment updated successfully');
    }

    /**
     * Update the payment status.
     */
    public function updateStatus(Request $request, User $user, Payment $payment)
    {
        // Ensure the payment belongs to the user
        if ($payment->user_id !== $user->id) {
            abort(404);
        }

        $validated = $request->validate([
            'status' => 'required|string|in:completed,pending,failed,refunded',
        ]);

        $payment->update(['status' => $validated['status']]);

        return response()->json([
            'success' => true,
            'message' => 'Payment status updated successfully',
            'payment' => $payment->fresh()
        ]);
    }

    /**
     * Remove the specified payment from storage.
     */
    public function destroy(User $user, Payment $payment)
    {
        // Ensure the payment belongs to the user
        if ($payment->user_id !== $user->id) {
            abort(404);
        }

        $payment->delete();

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Payment deleted successfully'
            ]);
        }

        return redirect()
            ->route('admin.users.payments.index', $user)
            ->with('success', 'Payment deleted successfully');
    }
}
