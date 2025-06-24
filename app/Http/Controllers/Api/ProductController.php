<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Interfaces\ProductServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ProductController extends Controller
{
    public function __construct(protected ProductServiceInterface $productService)
    {}
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        return response()->json(
            $this->productService->getAllProducts()->map->toArray()
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $productDto = $this->productService->createProduct($request->all());
        return response()->json($productDto->toArray(), Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $productDto = $this->productService->getProductById($id);
        if (!$productDto) {
            return response()->json(['message' => 'Product not found'], Response::HTTP_NOT_FOUND);
        }
        return response()->json($productDto->toArray());
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $productDto = $this->productService->updateProduct($id, $request->all());
        if (!$productDto) {
            return response()->json(['message' => 'Product not found'], Response::HTTP_NOT_FOUND);
        }
        return response()->json($productDto->toArray());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $deleted = $this->productService->deleteProduct($id);
        if (!$deleted) {
            return response()->json(['message' => 'Product not found'], Response::HTTP_NOT_FOUND);
        }
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
