<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Interfaces\CategoryServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CategoryController extends Controller
{
    public function __construct( protected CategoryServiceInterface $categoryService)
    {}
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        return response()->json(
            $this->categoryService->getAllCategories()->map->toArray()
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $categoryDto = $this->categoryService->createCategory($request->all());
        return response()->json($categoryDto->toArray(), Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $categoryDto = $this->categoryService->getCategoryById($id);
        if (!$categoryDto) {
            return response()->json(['message' => 'Category not found'], Response::HTTP_NOT_FOUND);
        }
        return response()->json($categoryDto->toArray());
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $categoryDto = $this->categoryService->updateCategory($id, $request->all());
        if (!$categoryDto) {
            return response()->json(['message' => 'Category not found'], Response::HTTP_NOT_FOUND);
        }
        return response()->json($categoryDto->toArray());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $deleted = $this->categoryService->deleteCategory($id);
        if (!$deleted) {
            return response()->json(['message' => 'Category not found'], Response::HTTP_NOT_FOUND);
        }
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
