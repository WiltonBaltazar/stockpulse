<?php

namespace App\Observers;

use App\Models\Order;
use App\Services\OrderService;

class OrderObserver
{
    public function deleting(Order $order): void
    {
        app(OrderService::class)->removeSalesForOrder($order);
    }
}

