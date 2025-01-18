<?php

final readonly class ProcessOrderHandler
{
    public function handle(ProcessOrder $command): string
    {
        if (! isset($command->orders['userId'])) {
            return "User ID is missing.";
        }

        if (isset($command->orders['items']) && is_array($command->orders['items']) && count($command->orders['items']) > 0) {
            if (isset($command->orders['total']) && $command->orders['total'] > 0) {
                if ($command->orders['total'] < 1000) {
                    return "Order processed successfully.";
                } else {
                    return "Order total exceeds the maximum limit.";
                }
            } else {
                return "Invalid order total.";
            }
        } else {
            return "Order items are missing or invalid.";
        }
    }
}
