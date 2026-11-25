<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Receivable;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PortalController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // Get all buyers
        $allCustomers = User::where('role', 'pembeli')
            ->withCount('customerTransactions')
            ->withSum(['receivables as total_active_debt' => function ($q) {
                $q->where('status', 'belum_lunas');
            }], 'remaining_debt')
            ->orderBy('name', 'asc')
            ->get();

        $selectedCustomerId = $request->query('customer_id');

        if ($user->role === 'pembeli') {
            $customer = $user;
        } elseif ($selectedCustomerId && $allCustomers->firstWhere('id', (int)$selectedCustomerId)) {
            $customer = $allCustomers->firstWhere('id', (int)$selectedCustomerId);
        } else {
            $customer = $allCustomers->first() ?? $user;
        }

        $customerId = $customer->id;

        $myReceivables = Receivable::with(['transaction', 'payments.receiver'])
            ->where('customer_id', $customerId)
            ->where('status', '!=', 'void')
            ->orderByRaw("(status = 'belum_lunas') DESC")
            ->orderBy('due_date', 'asc')
            ->get();

        $totalUnpaid = (float)$myReceivables->where('status', 'belum_lunas')->sum('remaining_debt');
        $creditLimit = (float)($customer->credit_limit ?? 0);
        $remainingCredit = max(0, $creditLimit - $totalUnpaid);

        $myTransactions = Transaction::with(['items', 'cashier', 'receivable'])
            ->where('customer_id', $customerId)
            ->orderBy('transaction_date', 'desc')
            ->limit(20)
            ->get();

        $categories = Category::withCount(['products' => function ($q) {
                $q->where('is_active', 1);
            }])
            ->orderBy('name', 'asc')
            ->get();

        $catalog = Product::with(['category', 'activeWholesaleRule'])
            ->where('is_active', 1)
            ->orderBy('name', 'asc')
            ->get();

        return view('portal.index', compact(
            'customer',
            'allCustomers',
            'myReceivables',
            'totalUnpaid',
            'creditLimit',
            'remainingCredit',
            'myTransactions',
            'categories',
            'catalog'
        ));
    }
}
