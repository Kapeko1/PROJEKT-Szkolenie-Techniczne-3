<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Interfaces\CategoryServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Categories', description: 'Category management endpoints')]
class CategoryController extends Controller
{
    public function __construct( protected CategoryServiceInterface $categoryService)
    {}
    #[OA\Get(
        path: '/api/categories',
        summary: 'Get all categories',
        description: 'Retrieve a list of all categories',
        tags: ['Categories'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful response',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'id', type: 'integer', example: 1),
                            new OA\Property(property: 'name', type: 'string', example: 'Electronics'),
                            new OA\Property(property: 'description', type: 'string', nullable: true, example: 'Electronic devices and accessories'),
                            new OA\Property(property: 'is_active', type: 'boolean', example: true),
                            new OA\Property(property: 'products_count', type: 'integer', nullable: true, example: 5)
                        ]
                    )
                )
            )
        ]
    )]
    public function index(): JsonResponse
    {
        return response()->json(
            $this->categoryService->getAllCategories()->map->toArray()
        );
    }

    #[OA\Post(
        path: '/api/categories',
        summary: 'Create a new category',
        description: 'Store a newly created category',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Electronics'),
                    new OA\Property(property: 'description', type: 'string', nullable: true, example: 'Electronic devices and accessories'),
                    new OA\Property(property: 'is_active', type: 'boolean', example: true)
                ]
            )
        ),
        tags: ['Categories'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Category created successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'name', type: 'string', example: 'Electronics'),
                        new OA\Property(property: 'description', type: 'string', nullable: true, example: 'Electronic devices and accessories'),
                        new OA\Property(property: 'is_active', type: 'boolean', example: true),
                        new OA\Property(property: 'products_count', type: 'integer', nullable: true, example: 0)
                    ]
                )
            )
        ]
    )]
    public function store(Request $request): JsonResponse
    {
        $categoryDto = $this->categoryService->createCategory($request->all());
        return response()->json($categoryDto->toArray(), Response::HTTP_CREATED);
    }

    #[OA\Get(
        path: '/api/categories/{id}',
        summary: 'Get category by ID',
        description: 'Retrieve a specific category by its ID',
        tags: ['Categories'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'Category ID',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful response',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'name', type: 'string', example: 'Electronics'),
                        new OA\Property(property: 'description', type: 'string', nullable: true, example: 'Electronic devices and accessories'),
                        new OA\Property(property: 'is_active', type: 'boolean', example: true),
                        new OA\Property(property: 'products_count', type: 'integer', nullable: true, example: 5)
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Category not found',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Category not found')
                    ]
                )
            )
        ]
    )]
    public function show(string $id): JsonResponse
    {
        $categoryDto = $this->categoryService->getCategoryById($id);
        if (!$categoryDto) {
            return response()->json(['message' => 'Category not found'], Response::HTTP_NOT_FOUND);
        }
        return response()->json($categoryDto->toArray());
    }

    #[OA\Put(
        path: '/api/categories/{id}',
        summary: 'Update category',
        description: 'Update a specific category by its ID',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Electronics'),
                    new OA\Property(property: 'description', type: 'string', nullable: true, example: 'Electronic devices and accessories'),
                    new OA\Property(property: 'is_active', type: 'boolean', example: true)
                ]
            )
        ),
        tags: ['Categories'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'Category ID',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Category updated successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'name', type: 'string', example: 'Electronics'),
                        new OA\Property(property: 'description', type: 'string', nullable: true, example: 'Electronic devices and accessories'),
                        new OA\Property(property: 'is_active', type: 'boolean', example: true),
                        new OA\Property(property: 'products_count', type: 'integer', nullable: true, example: 5)
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Category not found',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Category not found')
                    ]
                )
            )
        ]
    )]
    public function update(Request $request, string $id): JsonResponse
    {
        $categoryDto = $this->categoryService->updateCategory($id, $request->all());
        if (!$categoryDto) {
            return response()->json(['message' => 'Category not found'], Response::HTTP_NOT_FOUND);
        }
        return response()->json($categoryDto->toArray());
    }

    #[OA\Delete(
        path: '/api/categories/{id}',
        summary: 'Delete category',
        description: 'Delete a specific category by its ID',
        tags: ['Categories'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'Category ID',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            )
        ],
        responses: [
            new OA\Response(
                response: 204,
                description: 'Category deleted successfully'
            ),
            new OA\Response(
                response: 404,
                description: 'Category not found',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Category not found')
                    ]
                )
            )
        ]
    )]
    public function destroy(string $id): JsonResponse
    {
        $deleted = $this->categoryService->deleteCategory($id);
        if (!$deleted) {
            return response()->json(['message' => 'Category not found'], Response::HTTP_NOT_FOUND);
        }
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
