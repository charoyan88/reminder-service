<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderType;
use App\Services\OrderService;
use App\Services\ReminderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    protected OrderService $orderService;
    protected ReminderService $reminderService;

    public function __construct(OrderService $orderService, ReminderService $reminderService)
    {
        $this->orderService = $orderService;
        $this->reminderService = $reminderService;
    }

    /**
     * Create a new order
     */
    public function store(Request $request): JsonResponse
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'business_id' => 'required|exists:businesses,id',
            'order_type_id' => 'required|exists:order_types,id',
            'user_id' => 'required|exists:users,id',
            'external_order_id' => 'nullable|string',
            'application_date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Get the order type
        $orderType = OrderType::findOrFail($request->order_type_id);

        // Create the order
        $order = new Order($request->all());
        
        // Calculate expiration date based on order type
        $order->application_date = $request->application_date;
        $order->expiration_date = $order->calculateExpirationDate();
        $order->is_active = true;
        $order->save();

        // Check for existing orders of the same type for this user
        $existingOrders = $this->orderService->findExistingOrdersForUser(
            $request->user_id,
            $request->order_type_id
        );

        // If there are existing orders, mark them as replaced by this new order
        foreach ($existingOrders as $existingOrder) {
            if ($existingOrder->id !== $order->id) {
                $this->orderService->replaceOrder($existingOrder, $order);
                
                // Cancel any pending reminders for the replaced order
                $this->reminderService->cancelRemindersForOrder($existingOrder);
            }
        }

        // Schedule reminders for the new order
        $reminders = $this->reminderService->scheduleRemindersForOrder($order);

        return response()->json([
            'order' => $order,
            'scheduled_reminders' => count($reminders),
        ], 201);
    }

    /**
     * Update an existing order
     */
    public function update(Request $request, $id): JsonResponse
    {
        // Find the order
        $order = Order::findOrFail($id);
        
        // Validate request
        $validator = Validator::make($request->all(), [
            'is_active' => 'boolean',
            'application_date' => 'date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Update fields
        if (isset($request->is_active)) {
            $order->is_active = $request->is_active;
        }
        
        if (isset($request->application_date)) {
            $order->application_date = $request->application_date;
            $order->expiration_date = $order->calculateExpirationDate();
            
            // Cancel existing reminders and reschedule
            $this->reminderService->cancelRemindersForOrder($order, 'Order details updated');
            $reminders = $this->reminderService->scheduleRemindersForOrder($order);
        }
        
        $order->save();

        return response()->json([
            'order' => $order,
            'scheduled_reminders' => $reminders ?? 0,
        ]);
    }

    /**
     * Get orders expiring soon
     */
    public function getExpiringOrders(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'days' => 'required|integer|min:1',
            'order_type_id' => 'nullable|exists:order_types,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $orders = $this->orderService->findOrdersExpiringWithinDays(
            $request->days,
            $request->order_type_id
        );

        return response()->json(['orders' => $orders]);
    }

    /**
     * Get recently expired orders
     */
    public function getExpiredOrders(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'days' => 'required|integer|min:1',
            'order_type_id' => 'nullable|exists:order_types,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $orders = $this->orderService->findOrdersExpiredWithinDays(
            $request->days,
            $request->order_type_id
        );

        return response()->json(['orders' => $orders]);
    }
} 