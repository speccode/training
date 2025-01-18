<?php

final readonly class ProcessOrder
{
    public function __construct(
        public int $clientId,
        public int $orderId,
        public array $orders,
    ) {
    }
}
