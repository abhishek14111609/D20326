<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PaymentsExport;
use Carbon\Carbon;

class PaymentController extends Controller
{
    /**
     * Display a listing of all payments.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $payments = Payment::with('user')
            ->latest()
            ->paginate(15);

        return view('admin.payments.index', [
            'payments' => $payments
        ]);
    }

    /**
     * Show the form for creating a new payment.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $users = User::select('id', 'name', 'email')->get();
        return view('admin.payments.create', compact('users'));
    }

    /**
     * Store a newly created payment in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:0',
            'currency' => 'required|string|size:3',
            'payment_method' => 'required|string|max:50',
            'status' => 'required|in:pending,completed,failed,refunded',
            'transaction_id' => 'nullable|string|max:100|unique:payments,transaction_id',
            'description' => 'nullable|string|max:255',
        ]);

        $payment = Payment::create($validated);

        return redirect()
            ->route('admin.payments.show', $payment)
            ->with('success', 'Payment created successfully');
    }

    /**
     * Display the specified payment.
     *
     * @param  \App\Models\Payment  $payment
     * @return \Illuminate\View\View
     */
    public function show(Payment $payment)
    {
        $payment->load('user');
        return view('admin.payments.show', compact('payment'));
    }

    /**
     * Show the form for editing the specified payment.
     *
     * @param  \App\Models\Payment  $payment
     * @return \Illuminate\View\View
     */
    public function edit(Payment $payment)
    {
        $users = User::select('id', 'name', 'email')->get();
        return view('admin.payments.edit', compact('payment', 'users'));
    }

    /**
     * Update the specified payment in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Payment  $payment
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Payment $payment)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:0',
            'currency' => 'required|string|size:3',
            'payment_method' => 'required|string|max:50',
            'status' => 'required|in:pending,completed,failed,refunded',
            'transaction_id' => 'nullable|string|max:100|unique:payments,transaction_id,' . $payment->id,
            'description' => 'nullable|string|max:255',
        ]);

        $payment->update($validated);

        return redirect()
            ->route('admin.payments.show', $payment)
            ->with('success', 'Payment updated successfully');
    }

    /**
     * Remove the specified payment from storage.
     *
     * @param  \App\Models\Payment  $payment
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Payment $payment)
    {
        $payment->delete();

        return redirect()
            ->route('admin.payments.index')
            ->with('success', 'Payment deleted successfully');
    }

    /**
     * Export payments to Excel
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function export(Request $request)
    {
        $fileName = 'payments-export-' . Carbon::now()->format('Y-m-d-H-i-s') . '.xlsx';
        
        // If you have a dedicated PaymentsExport class (recommended for complex exports)
        if (class_exists('App\Exports\PaymentsExport')) {
            return Excel::download(new PaymentsExport($request), $fileName);
        }
        
        // Simple export directly from controller
        return Excel::download(
            function($excel) use ($request) {
                $payments = Payment::with('user')
                    ->when($request->filled('status'), function($query) use ($request) {
                        return $query->where('status', $request->status);
                    })
                    ->when($request->filled('from_date'), function($query) use ($request) {
                        return $query->whereDate('created_at', '>=', $request->from_date);
                    })
                    ->when($request->filled('to_date'), function($query) use ($request) {
                        return $query->whereDate('created_at', '<=', $request->to_date);
                    })
                    ->get();
                
                $excel->sheet('Payments', function($sheet) use ($payments) {
                    $sheet->fromArray($payments->map(function($payment) {
                        return [
                            'ID' => $payment->id,
                            'Transaction ID' => $payment->transaction_id,
                            'User' => $payment->user ? $payment->user->name : 'N/A',
                            'Amount' => '$' . number_format($payment->amount, 2),
                            'Currency' => strtoupper($payment->currency),
                            'Status' => ucfirst($payment->status),
                            'Payment Method' => $payment->payment_method,
                            'Created At' => $payment->created_at->format('M d, Y h:i A'),
                        ];
                    }));
                    
                    // Add headers
                    $sheet->prependRow(1, [
                        'ID', 'Transaction ID', 'User', 'Amount', 'Currency', 'Status', 'Payment Method', 'Created At'
                    ]);
                    
                    // Style the header row
                    $sheet->row(1, function($row) {
                        $row->setFontWeight('bold');
                        $row->setBackground('#f5f5f5');
                    });
                });
            },
            $fileName
        );
    }
}
