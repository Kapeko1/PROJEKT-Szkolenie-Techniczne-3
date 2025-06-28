<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Interfaces\ProductServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Products', description: 'Product management endpoints')]
class ProductController extends Controller
{
    public function __construct(protected ProductServiceInterface $productService)
    {}
    #[OA\Get(
        path: '/api/products',
        summary: 'Get all products',
        description: 'Retrieve a list of all products',
        tags: ['Products'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful response',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'id', type: 'integer', example: 1),
                            new OA\Property(property: 'name', type: 'string', example: 'iPhone 15'),
                            new OA\Property(property: 'description', type: 'string', nullable: true, example: 'Latest iPhone model'),
                            new OA\Property(property: 'sku', type: 'string', example: 'IPH15-128'),
                            new OA\Property(property: 'price', type: 'number', format: 'float', example: 999.99),
                            new OA\Property(property: 'quantity', type: 'integer', example: 50),
                            new OA\Property(property: 'category_id', type: 'integer', example: 1),
                            new OA\Property(property: 'category_name', type: 'string', nullable: true, example: 'Electronics'),
                            new OA\Property(property: 'is_active', type: 'boolean', example: true)
                        ]
                    )
                )
            )
        ]
    )]
    public function index(): JsonResponse
    {
        return response()->json(
            $this->productService->getAllProducts()->map->toArray()
        );
    }

    #[OA\Post(
        path: '/api/products',
        summary: 'Create a new product',
        description: 'Store a newly created product',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'sku', 'price', 'quantity', 'category_id'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'iPhone 15'),
                    new OA\Property(property: 'description', type: 'string', nullable: true, example: 'Latest iPhone model'),
                    new OA\Property(property: 'sku', type: 'string', example: 'IPH15-128'),
                    new OA\Property(property: 'price', type: 'number', format: 'float', example: 999.99),
                    new OA\Property(property: 'quantity', type: 'integer', example: 50),
                    new OA\Property(property: 'category_id', type: 'integer', example: 1),
                    new OA\Property(property: 'is_active', type: 'boolean', example: true)
                ]
            )
        ),
        tags: ['Products'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Product created successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'name', type: 'string', example: 'iPhone 15'),
                        new OA\Property(property: 'description', type: 'string', nullable: true, example: 'Latest iPhone model'),
                        new OA\Property(property: 'sku', type: 'string', example: 'IPH15-128'),
                        new OA\Property(property: 'price', type: 'number', format: 'float', example: 999.99),
                        new OA\Property(property: 'quantity', type: 'integer', example: 50),
                        new OA\Property(property: 'category_id', type: 'integer', example: 1),
                        new OA\Property(property: 'category_name', type: 'string', nullable: true, example: 'Electronics'),
                        new OA\Property(property: 'is_active', type: 'boolean', example: true)
                    ]
                )
            )
        ]
    )]
    public function store(Request $request): JsonResponse
    {
        $productDto = $this->productService->createProduct($request->all());
        return response()->json($productDto->toArray(), Response::HTTP_CREATED);
    }

    #[OA\Get(
        path: '/api/products/{id}',
        summary: 'Get product by ID',
        description: 'Retrieve a specific product by its ID',
        tags: ['Products'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'Product ID',
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
                        new OA\Property(property: 'name', type: 'string', example: 'iPhone 15'),
                        new OA\Property(property: 'description', type: 'string', nullable: true, example: 'Latest iPhone model'),
                        new OA\Property(property: 'sku', type: 'string', example: 'IPH15-128'),
                        new OA\Property(property: 'price', type: 'number', format: 'float', example: 999.99),
                        new OA\Property(property: 'quantity', type: 'integer', example: 50),
                        new OA\Property(property: 'category_id', type: 'integer', example: 1),
                        new OA\Property(property: 'category_name', type: 'string', nullable: true, example: 'Electronics'),
                        new OA\Property(property: 'is_active', type: 'boolean', example: true)
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Product not found',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Product not found')
                    ]
                )
            )
        ]
    )]
    public function show(string $id): JsonResponse
    {
        $productDto = $this->productService->getProductById($id);
        if (!$productDto) {
            return response()->json(['message' => 'Product not found'], Response::HTTP_NOT_FOUND);
        }
        return response()->json($productDto->toArray());
    }

    #[OA\Put(
        path: '/api/products/{id}',
        summary: 'Update product',
        description: 'Update a specific product by its ID',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'iPhone 15'),
                    new OA\Property(property: 'description', type: 'string', nullable: true, example: 'Latest iPhone model'),
                    new OA\Property(property: 'sku', type: 'string', example: 'IPH15-128'),
                    new OA\Property(property: 'price', type: 'number', format: 'float', example: 999.99),
                    new OA\Property(property: 'quantity', type: 'integer', example: 50),
                    new OA\Property(property: 'category_id', type: 'integer', example: 1),
                    new OA\Property(property: 'is_active', type: 'boolean', example: true)
                ]
            )
        ),
        tags: ['Products'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'Product ID',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Product updated successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'name', type: 'string', example: 'iPhone 15'),
                        new OA\Property(property: 'description', type: 'string', nullable: true, example: 'Latest iPhone model'),
                        new OA\Property(property: 'sku', type: 'string', example: 'IPH15-128'),
                        new OA\Property(property: 'price', type: 'number', format: 'float', example: 999.99),
                        new OA\Property(property: 'quantity', type: 'integer', example: 50),
                        new OA\Property(property: 'category_id', type: 'integer', example: 1),
                        new OA\Property(property: 'category_name', type: 'string', nullable: true, example: 'Electronics'),
                        new OA\Property(property: 'is_active', type: 'boolean', example: true)
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Product not found',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Product not found')
                    ]
                )
            )
        ]
    )]
    public function update(Request $request, string $id): JsonResponse
    {
        $productDto = $this->productService->updateProduct($id, $request->all());
        if (!$productDto) {
            return response()->json(['message' => 'Product not found'], Response::HTTP_NOT_FOUND);
        }
        return response()->json($productDto->toArray());
    }

    #[OA\Delete(
        path: '/api/products/{id}',
        summary: 'Delete product',
        description: 'Delete a specific product by its ID',
        tags: ['Products'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'Product ID',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            )
        ],
        responses: [
            new OA\Response(
                response: 204,
                description: 'Product deleted successfully'
            ),
            new OA\Response(
                response: 404,
                description: 'Product not found',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Product not found')
                    ]
                )
            )
        ]
    )]
    public function destroy(string $id): JsonResponse
    {
        $deleted = $this->productService->deleteProduct($id);
        if (!$deleted) {
            return response()->json(['message' => 'Product not found'], Response::HTTP_NOT_FOUND);
        }
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
