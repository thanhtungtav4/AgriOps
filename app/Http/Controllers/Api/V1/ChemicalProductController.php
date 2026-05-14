<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\ChemicalProduct;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChemicalProductController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = ChemicalProduct::with(['farm']);

        // Non-admin can only see their farm's products or global products
        if (!$user->isAdmin()) {
            $query->where(function ($q) use ($user) {
                $q->whereNull('farm_id')->orWhere('farm_id', $user->farm_id);
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('active_ingredient', 'like', "%{$search}%");
            });
        }

        $products = $query->orderBy('name')->paginate($request->input('per_page', 50));

        return $this->success($products);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!in_array($user->role, ['admin', 'farm_owner', 'farm_manager'])) {
            return $this->forbiddenError('You do not have permission to create chemical products.');
        }

        $validated = $request->validate([
            'farm_id' => 'nullable|exists:farms,id',
            'name' => 'required|string|max:255',
            'active_ingredient' => 'required|string|max:255',
            'type' => 'required|in:pesticide,herbicide,fungicide,fertilizer,biological,other',
            'formulation' => 'nullable|string|max:100',
            'registration_number' => 'nullable|string|max:100',
            'manufacturer' => 'nullable|string|max:255',
            'supplier' => 'nullable|string|max:255',
            'unit' => 'required|string|max:50',
            'stock_quantity' => 'nullable|numeric|min:0',
            'min_stock_level' => 'nullable|numeric|min:0',
            'price_per_unit' => 'nullable|numeric|min:0',
            'usage_instructions' => 'nullable|string|max:5000',
            'safety_instructions' => 'nullable|string|max:5000',
            'storage_conditions' => 'nullable|string|max:1000',
            'expiry_date' => 'nullable|date',
            'is_active' => 'nullable|boolean',
            'notes' => 'nullable|string|max:2000',
        ]);

        // If creating for specific farm, validate farm ownership
        if (!empty($validated['farm_id']) && !$user->isAdmin() && $user->farm_id !== $validated['farm_id']) {
            return $this->forbiddenError('You can only create products for your own farm.');
        }

        // If no farm specified, assign to user's farm for non-admin
        if (empty($validated['farm_id']) && !$user->isAdmin()) {
            $validated['farm_id'] = $user->farm_id;
        }

        $product = ChemicalProduct::create($validated);

        return $this->success(
            $product->load(['farm']),
            201,
            'Chemical product created'
        );
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $product = ChemicalProduct::with(['farm'])->findOrFail($id);
        $user = $request->user();

        // Non-admin users can only view products from their own farm or global products
        if (!$user->isAdmin()) {
            if ($product->farm_id && $product->farm_id !== $user->farm_id) {
                return $this->forbiddenError('You do not have permission to view this product.');
            }
        }

        return $this->success($product);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $product = ChemicalProduct::findOrFail($id);
        $user = $request->user();

        if (!in_array($user->role, ['admin', 'farm_owner', 'farm_manager'])) {
            return $this->forbiddenError('You do not have permission to update chemical products.');
        }

        if (!$user->isAdmin() && (!$product->farm_id || $product->farm_id !== $user->farm_id)) {
            return $this->forbiddenError('You can only update products from your own farm.');
        }

        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'active_ingredient' => 'nullable|string|max:255',
            'type' => 'nullable|in:pesticide,herbicide,fungicide,fertilizer,biological,other',
            'formulation' => 'nullable|string|max:100',
            'registration_number' => 'nullable|string|max:100',
            'manufacturer' => 'nullable|string|max:255',
            'supplier' => 'nullable|string|max:255',
            'unit' => 'nullable|string|max:50',
            'stock_quantity' => 'nullable|numeric|min:0',
            'min_stock_level' => 'nullable|numeric|min:0',
            'price_per_unit' => 'nullable|numeric|min:0',
            'usage_instructions' => 'nullable|string|max:5000',
            'safety_instructions' => 'nullable|string|max:5000',
            'storage_conditions' => 'nullable|string|max:1000',
            'expiry_date' => 'nullable|date',
            'is_active' => 'nullable|boolean',
            'notes' => 'nullable|string|max:2000',
        ]);

        $product->update($validated);

        return $this->success(
            $product->fresh()->load(['farm']),
            200,
            'Chemical product updated'
        );
    }

    public function updateStock(Request $request, int $id): JsonResponse
    {
        $product = ChemicalProduct::findOrFail($id);
        $user = $request->user();

        if (!in_array($user->role, ['admin', 'farm_owner', 'farm_manager', 'warehouse'])) {
            return $this->forbiddenError('You do not have permission to update stock.');
        }

        // Non-admin users can only update stock for products from their own farm
        if (!$user->isAdmin()) {
            if (!$product->farm_id || $product->farm_id !== $user->farm_id) {
                return $this->forbiddenError('You can only update stock for products from your own farm.');
            }
        }

        $validated = $request->validate([
            'adjustment' => 'required|numeric', // positive or negative
        ]);

        $newQuantity = $product->stock_quantity + $validated['adjustment'];

        if ($newQuantity < 0) {
            return $this->domainError('Stock cannot be negative.', [], 'INVALID_STOCK', 422);
        }

        $product->update(['stock_quantity' => $newQuantity]);

        return $this->success(
            $product->fresh()->load(['farm']),
            200,
            'Stock updated'
        );
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $product = ChemicalProduct::findOrFail($id);

        if (!auth()->user()->isAdmin()) {
            return $this->forbiddenError('Only admins can delete chemical products.');
        }

        $product->delete();

        return $this->success(null, 200, 'Chemical product deleted');
    }

    public function lowStock(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = ChemicalProduct::active()->lowStock();

        if (!$user->isAdmin()) {
            $query->where(function ($q) use ($user) {
                $q->whereNull('farm_id')->orWhere('farm_id', $user->farm_id);
            });
        }

        $products = $query->orderBy('stock_quantity')->get();

        return $this->success($products);
    }
}
