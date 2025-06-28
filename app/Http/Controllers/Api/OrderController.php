<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Interfaces\OrderServiceInterface;
use App\Services\Interfaces\ProductServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Orders', description: 'Order management endpoints')]
class OrderController extends Controller
{
    public function __construct(protected OrderServiceInterface $orderService)
    {}

    #[OA\Get(
        path: '/api/orders',
        summary: 'Get all orders',
        description: 'Retrieve a list of all orders',
        tags: ['Orders'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful response',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'id', type: 'integer', example: 1),
                            new OA\Property(property: 'product_id', type: 'integer', example: 1),
                            new OA\Property(property: 'product_name', type: 'string', example: 'iPhone 15'),
                            new OA\Property(property: 'customer_name', type: 'string', example: 'John Doe'),
                            new OA\Property(property: 'customer_email', type: 'string', format: 'email', example: 'john@example.com'),
                            new OA\Property(property: 'quantity', type: 'integer', example: 2),
                            new OA\Property(property: 'unit_price', type: 'number', format: 'float', example: 999.99),
                            new OA\Property(property: 'total_price', type: 'number', format: 'float', example: 1999.98),
                            new OA\Property(property: 'status', type: 'string', example: 'pending'),
                            new OA\Property(property: 'order_date', type: 'string', format: 'date-time', example: '2024-01-15T10:30:00Z')
                        ]
                    )
                )
            )
        ]
    )]
    public function index(): JsonResponse
    {
        return response()->json(
            $this->orderService->getAllOrders()->map->toArray()
        );
    }

    #[OA\Post(
        path: '/api/orders',
        summary: 'Create a new order',
        description: 'Store a newly created order',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['product_id', 'customer_name', 'customer_email', 'quantity'],
                properties: [
                    new OA\Property(property: 'product_id', type: 'integer', example: 1),
                    new OA\Property(property: 'customer_name', type: 'string', example: 'John Doe'),
                    new OA\Property(property: 'customer_email', type: 'string', format: 'email', example: 'john@example.com'),
                    new OA\Property(property: 'quantity', type: 'integer', example: 2),
                    new OA\Property(property: 'status', type: 'string', example: 'pending')
                ]
            )
        ),
        tags: ['Orders'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Order created successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'product_id', type: 'integer', example: 1),
                        new OA\Property(property: 'product_name', type: 'string', example: 'iPhone 15'),
                        new OA\Property(property: 'customer_name', type: 'string', example: 'John Doe'),
                        new OA\Property(property: 'customer_email', type: 'string', format: 'email', example: 'john@example.com'),
                        new OA\Property(property: 'quantity', type: 'integer', example: 2),
                        new OA\Property(property: 'unit_price', type: 'number', format: 'float', example: 999.99),
                        new OA\Property(property: 'total_price', type: 'number', format: 'float', example: 1999.98),
                        new OA\Property(property: 'status', type: 'string', example: 'pending'),
                        new OA\Property(property: 'order_date', type: 'string', format: 'date-time', example: '2024-01-15T10:30:00Z')
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Bad request',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Validation error or business logic error')
                    ]
                )
            )
        ]
    )]
    public function store(Request $request): JsonResponse
    {
        try {
            $orderDto = $this->orderService->createOrder($request->all());
            return response()->json($orderDto->toArray(), Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[OA\Get(
        path: '/api/orders/{id}',
        summary: 'Get order by ID',
        description: 'Retrieve a specific order by its ID',
        tags: ['Orders'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'Order ID',
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
                        new OA\Property(property: 'product_id', type: 'integer', example: 1),
                        new OA\Property(property: 'product_name', type: 'string', example: 'iPhone 15'),
                        new OA\Property(property: 'customer_name', type: 'string', example: 'John Doe'),
                        new OA\Property(property: 'customer_email', type: 'string', format: 'email', example: 'john@example.com'),
                        new OA\Property(property: 'quantity', type: 'integer', example: 2),
                        new OA\Property(property: 'unit_price', type: 'number', format: 'float', example: 999.99),
                        new OA\Property(property: 'total_price', type: 'number', format: 'float', example: 1999.98),
                        new OA\Property(property: 'status', type: 'string', example: 'pending'),
                        new OA\Property(property: 'order_date', type: 'string', format: 'date-time', example: '2024-01-15T10:30:00Z')
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Order not found',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Order not found')
                    ]
                )
            )
        ]
    )]
    public function show(string $id): JsonResponse
    {
        $orderDto = $this->orderService->getOrderById($id);
        if (!$orderDto) {
            return response()->json(['message' => 'Order not found'], Response::HTTP_NOT_FOUND);
        }
        return response()->json($orderDto->toArray());
    }

    #[OA\Put(
        path: '/api/orders/{id}',
        summary: 'Update order',
        description: 'Update a specific order by its ID',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'product_id', type: 'integer', example: 1),
                    new OA\Property(property: 'customer_name', type: 'string', example: 'John Doe'),
                    new OA\Property(property: 'customer_email', type: 'string', format: 'email', example: 'john@example.com'),
                    new OA\Property(property: 'quantity', type: 'integer', example: 2),
                    new OA\Property(property: 'status', type: 'string', example: 'shipped')
                ]
            )
        ),
        tags: ['Orders'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'Order ID',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Order updated successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'product_id', type: 'integer', example: 1),
                        new OA\Property(property: 'product_name', type: 'string', example: 'iPhone 15'),
                        new OA\Property(property: 'customer_name', type: 'string', example: 'John Doe'),
                        new OA\Property(property: 'customer_email', type: 'string', format: 'email', example: 'john@example.com'),
                        new OA\Property(property: 'quantity', type: 'integer', example: 2),
                        new OA\Property(property: 'unit_price', type: 'number', format: 'float', example: 999.99),
                        new OA\Property(property: 'total_price', type: 'number', format: 'float', example: 1999.98),
                        new OA\Property(property: 'status', type: 'string', example: 'shipped'),
                        new OA\Property(property: 'order_date', type: 'string', format: 'date-time', example: '2024-01-15T10:30:00Z')
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Bad request',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Validation error or business logic error')
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Order not found',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Order not found')
                    ]
                )
            )
        ]
    )]
    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $orderDto = $this->orderService->updateOrder($id, $request->all());
            if (!$orderDto) {
                return response()->json(['message' => 'Order not found'], Response::HTTP_NOT_FOUND);
            }
            return response()->json($orderDto->toArray());
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[OA\Delete(
        path: '/api/orders/{id}',
        summary: 'Delete order',
        description: 'Delete a specific order by its ID',
        tags: ['Orders'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'Order ID',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            )
        ],
        responses: [
            new OA\Response(
                response: 204,
                description: 'Order deleted successfully'
            ),
            new OA\Response(
                response: 404,
                description: 'Order not found',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Order not found')
                    ]
                )
            )
        ]
    )]
    public function destroy(string $id): JsonResponse
    {
        $deleted = $this->orderService->deleteOrder($id);
        if (!$deleted) {
            return response()->json(['message' => 'Order not found'], Response::HTTP_NOT_FOUND);
        }
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
