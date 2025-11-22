<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Services\CustomerService;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class CustomerController extends Controller
{
    protected $customerService;

    public function __construct(CustomerService $customerService)
    {
        $this->customerService = $customerService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Customer::withCount([
                'transactions as outbound_count' => function ($query) {
                    $query->where('type', \App\Enums\TransactionType::OUTBOUND);
                }
            ])->select('customers.*');

            return DataTables::of($query)
                ->editColumn('phone', function ($customer) {
                    return $customer->phone ?? '-';
                })
                ->editColumn('address', function ($customer) {
                    return $customer->address ?? '-';
                })
                ->addColumn('outbound_count', function ($customer) {
                    return $customer->outbound_count ?? 0;
                })
                ->addColumn('action', function ($customer) {
                    return view('features.customers.partials.action-buttons', compact('customer'))->render();
                })
                ->editColumn('created_at', function ($customer) {
                    return $customer->created_at->format('Y-m-d');
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('features.customers.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('features.customers.partials.form', [
            'customer' => null,
            'isEdit' => false,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate(CustomerService::getCreateRules());

        try {
            $customer = $this->customerService->create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Customer berhasil ditambahkan.',
                'data' => $customer,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Customer $customer)
    {
        $customer->load('outbounds');
        return view('features.customers.partials.show', compact('customer'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Customer $customer)
    {
        return view('features.customers.partials.form', [
            'customer' => $customer,
            'isEdit' => true,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate(CustomerService::getUpdateRules($customer));

        try {
            $customer = $this->customerService->update($customer, $validated);

            return response()->json([
                'success' => true,
                'message' => 'Customer berhasil diperbarui.',
                'data' => $customer,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer)
    {
        try {
            $this->customerService->delete($customer);

            return response()->json([
                'success' => true,
                'message' => 'Customer berhasil dihapus.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}

