<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Receivable;
use App\Models\StoreSetting;
use App\Models\User;
use App\Services\CheckoutService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PosController extends Controller
{
    public function index()
    {
        $categories = Category::orderBy('name', 'asc')->get();

        $products = Product::with(['category', 'activeWholesaleRule'])
            ->where('is_active', 1)
            ->orderBy('name', 'asc')
            ->get();

        $customers = User::where('role', 'pembeli')
            ->where('is_active', 1)
            ->withSum(['receivables as current_debt' => function ($q) {
                $q->where('status', 'belum_lunas');
            }], 'remaining_debt')
            ->orderBy('name', 'asc')
            ->get();

        $store = StoreSetting::allSettings();

        return view('pos.index', compact('categories', 'products', 'customers', 'store'));
    }

    public function checkout(Request $request, CheckoutService $checkoutService)
    {
        $user = Auth::user();
        if (!in_array($user->role, ['owner', 'kasir'], true)) {
            return response()->json(['success' => false, 'message' => 'Hanya Kasir atau Owner yang dapat memproses transaksi POS.'], 403);
        }

        try {
            $data = $request->all();
            $result = $checkoutService->processCheckout($data, $user);

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil diproses.',
                'transaction_id' => $result['transaction_id'],
                'invoice_number' => $result['invoice_number'],
                'transaction' => $result['transaction'],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage(), 'errors' => $e->errors()], 422);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }
}
