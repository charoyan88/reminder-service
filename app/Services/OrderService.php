<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderType;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class OrderService
{
    /**
     * Find orders that will expire within the given number of days
     * 
     * @param int $days Number of days from now
     * @param int|null $orderTypeId Optional filter by order type
     * @return Collection|Order[] Collection of orders that will expire
     */
    public function findOrdersExpiringWithinDays(int $days, ?int $orderTypeId = null): Collection
    {
        $targetDate = Carbon::now()->addDays($days);
        
        $query = Order::query()
            ->where('is_active', true)
            ->where('expiration_date', '<=', $targetDate)
            ->whereNull('replaced_by_order_id');
            
        if ($orderTypeId) {
            $query->where('order_type_id', $orderTypeId);
        }
        
        return $query->get();
    }
    
    /**
     * Find orders that expired in the past within the given number of days
     * 
     * @param int $days Number of days before now
     * @param int|null $orderTypeId Optional filter by order type
     * @return Collection|Order[] Collection of expired orders
     */
    public function findOrdersExpiredWithinDays(int $days, ?int $orderTypeId = null): Collection
    {
        $startDate = Carbon::now()->subDays($days);
        $endDate = Carbon::now();
        
        $query = Order::query()
            ->where('is_active', true)
            ->whereBetween('expiration_date', [$startDate, $endDate])
            ->whereNull('replaced_by_order_id');
            
        if ($orderTypeId) {
            $query->where('order_type_id', $orderTypeId);
        }
        
        return $query->get();
    }
    
    /**
     * Mark an existing order as replaced by a new order
     * 
     * @param Order $existingOrder The existing order
     * @param Order $newOrder The new order that replaces it
     * @return bool Whether the operation was successful
     */
    public function replaceOrder(Order $existingOrder, Order $newOrder): bool
    {
        $existingOrder->replaced_by_order_id = $newOrder->id;
        return $existingOrder->save();
    }
    
    /**
     * Find existing active orders of the same type for a user
     * 
     * @param int $userId The user ID
     * @param int $orderTypeId The order type ID
     * @return Collection|Order[] Collection of existing orders
     */
    public function findExistingOrdersForUser(int $userId, int $orderTypeId): Collection
    {
        return Order::query()
            ->where('user_id', $userId)
            ->where('order_type_id', $orderTypeId)
            ->where('is_active', true)
            ->whereNull('replaced_by_order_id')
            ->get();
    }
} 