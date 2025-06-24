<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Interfaces\OrderServiceInterface;
use App\Services\Interfaces\ProductServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class OrderController extends Controller
{
    public function __construct(protected OrderServiceInterface $orderService)
    {}

    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        return response()->json(
            $this->orderService->getAllOrders()->map->toArray()
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $orderDto = $this->orderService->createOrder($request->all());
            return response()->json($orderDto->toArray(), Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $orderDto = $this->orderService->getOrderById($id);
        if (!$orderDto) {
            return response()->json(['message' => 'Order not found'], Response::HTTP_NOT_FOUND);
        }
        return response()->json($orderDto->toArray());
    }

    /**
     * Update the specified resource in storage.
     */
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

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $deleted = $this->orderService->deleteOrder($id);
        if (!$deleted) {
            return response()->json(['message' => 'Order not found'], Response::HTTP_NOT_FOUND);
        }
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
