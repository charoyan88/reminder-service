<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\StoreRequest;
use App\Http\Requests\Order\UpdateRequest;
use App\Http\Requests\Order\ExpiringRequest;
use App\Http\Requests\Order\ExpiredRequest;
use App\Http\Resources\OrderResource;
use App\Http\Resources\OrderCollection;
use App\Models\Order;
use App\Models\OrderType;
use App\Services\OrderService;
use App\Services\ReminderService;
use Illuminate\Support\Facades\Log;

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
    public function store(StoreRequest $request): OrderResource
    {
        Log::info('Creating new order', [
            'user_id' => $request->user_id,
            'business_id' => $request->business_id,
            'order_type_id' => $request->order_type_id
        ]);

        // Get the validated data
        $validatedData = $request->validated();

        try {
            // Get the order type
            $orderType = OrderType::findOrFail($validatedData['order_type_id']);

            // Create the order
            $order = new Order($validatedData);
            
            // Calculate expiration date based on order type
            $order->application_date = $validatedData['application_date'];
            $order->expiration_date = $order->calculateExpirationDate();
            $order->is_active = true;
            $order->save();

            Log::info('Order created successfully', [
                'order_id' => $order->id,
                'expiration_date' => $order->expiration_date
            ]);

            // Check for existing orders of the same type for this user
            $existingOrders = $this->orderService->findExistingOrdersForUser(
                $validatedData['user_id'],
                $validatedData['order_type_id']
            );

            // If there are existing orders, mark them as replaced by this new order
            foreach ($existingOrders as $existingOrder) {
                if ($existingOrder->id !== $order->id) {
                    $this->orderService->replaceOrder($existingOrder, $order);
                    
                    Log::info('Existing order marked as replaced', [
                        'replaced_order_id' => $existingOrder->id,
                        'new_order_id' => $order->id
                    ]);
                    
                    // Cancel any pending reminders for the replaced order
                    $this->reminderService->cancelRemindersForOrder($existingOrder);
                }
            }

            // Schedule reminders for the new order
            $reminders = $this->reminderService->scheduleRemindersForOrder($order);

            Log::info('Reminders scheduled for order', [
                'order_id' => $order->id,
                'reminder_count' => count($reminders)
            ]);

            // Load relationships
            $order->load(['orderType', 'user', 'business']);

            return (new OrderResource($order))
                ->additional([
                    'scheduled_reminders' => count($reminders),
                    'message' => 'Order created successfully'
                ])
                ->response()
                ->setStatusCode(201);

        } catch (\Exception $e) {
            Log::error('Failed to create order', [
                'error' => $e->getMessage(),
                'user_id' => $validatedData['user_id'],
                'business_id' => $validatedData['business_id']
            ]);
            throw $e;
        }
    }

    /**
     * Update an existing order
     */
    public function update(UpdateRequest $request, $id): OrderResource
    {
        Log::info('Updating order', [
            'order_id' => $id,
            'changes' => $request->validated()
        ]);

        try {
            // Find the order
            $order = Order::with(['orderType', 'user', 'business'])->findOrFail($id);
            
            // Get the validated data
            $validatedData = $request->validated();

            // Update fields
            if (isset($validatedData['is_active'])) {
                $order->is_active = $validatedData['is_active'];
                Log::info('Order status updated', [
                    'order_id' => $id,
                    'is_active' => $validatedData['is_active']
                ]);
            }
            
            if (isset($validatedData['application_date'])) {
                $order->application_date = $validatedData['application_date'];
                $order->expiration_date = $order->calculateExpirationDate();
                
                Log::info('Order dates updated', [
                    'order_id' => $id,
                    'application_date' => $validatedData['application_date'],
                    'new_expiration_date' => $order->expiration_date
                ]);
                
                // Cancel existing reminders and reschedule
                $this->reminderService->cancelRemindersForOrder($order, 'Order details updated');
                $reminders = $this->reminderService->scheduleRemindersForOrder($order);

                Log::info('Order reminders rescheduled', [
                    'order_id' => $id,
                    'new_reminder_count' => count($reminders ?? [])
                ]);
            }
            
            $order->save();

            return (new OrderResource($order))
                ->additional([
                    'scheduled_reminders' => $reminders ?? 0,
                    'message' => 'Order updated successfully'
                ]);

        } catch (\Exception $e) {
            Log::error('Failed to update order', [
                'order_id' => $id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Get orders expiring soon
     */
    public function getExpiringOrders(ExpiringRequest $request): OrderCollection
    {
        $validatedData = $request->validated();

        Log::info('Fetching expiring orders', [
            'days' => $validatedData['days'],
            'order_type_id' => $validatedData['order_type_id'] ?? 'all'
        ]);

        try {
            $orders = $this->orderService->findOrdersExpiringWithinDays(
                $validatedData['days'],
                $validatedData['order_type_id'] ?? null
            );

            // Load relationships
            $orders->load(['orderType', 'user', 'business']);

            Log::info('Successfully fetched expiring orders', [
                'count' => $orders->count(),
                'days' => $validatedData['days']
            ]);

            return new OrderCollection($orders);

        } catch (\Exception $e) {
            Log::error('Failed to fetch expiring orders', [
                'days' => $validatedData['days'],
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Get recently expired orders
     */
    public function getExpiredOrders(ExpiredRequest $request): OrderCollection
    {
        $validatedData = $request->validated();

        Log::info('Fetching expired orders', [
            'days' => $validatedData['days'],
            'order_type_id' => $validatedData['order_type_id'] ?? 'all'
        ]);

        try {
            $orders = $this->orderService->findOrdersExpiredWithinDays(
                $validatedData['days'],
                $validatedData['order_type_id'] ?? null
            );

            // Load relationships
            $orders->load(['orderType', 'user', 'business']);

            Log::info('Successfully fetched expired orders', [
                'count' => $orders->count(),
                'days' => $validatedData['days']
            ]);

            return new OrderCollection($orders);

        } catch (\Exception $e) {
            Log::error('Failed to fetch expired orders', [
                'days' => $validatedData['days'],
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
} 