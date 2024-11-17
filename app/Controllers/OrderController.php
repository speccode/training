<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repositories\LegacyRepository;
use PDO; // Simulating an injected database connection

class OrderController
{
    public function __construct(
        private readonly LegacyRepository $legacyRepository
    ) {
    }

    public function processOrder(array $request): array
    {
        // Step 1: Validate Input (Primitive obsession!)
        if (!isset($request['userId']) || !isset($request['items']) || !is_array($request['items'])) {
            return [
                'status' => 'error',
                'message' => "Invalid input! Missing userId or items.",
            ];
        }

        // Step 2: Calculate Base Order Total
        $total = 0;
        foreach ($request['items'] as $item) {
            if (!isset($item['price']) || !isset($item['quantity'])) {
                return [
                    'status' => 'error',
                    'message' => "Invalid item data.",
                ];
            }
            $total += $item['price'] * $item['quantity'];
        }

        // TODO: Add tax calculations later

        // Step 3: Apply Discounts (Complex IF hell)
        $discount = 0;
        if (isset($request['promoCode']) && $request['promoCode'] === 'DISCOUNT10') {
            // 10% discount for promoCode
            $discount = $total * 0.1;
        } elseif ($total > 100 && (!isset($request['promoCode']) || $request['promoCode'] !== 'DISCOUNT10')) {
            // 5% discount for orders over $100
            $discount = $total * 0.05;
        } elseif (count($request['items']) > 5 && $total < 50) {
            // Flat $10 discount for small total but many items
            $discount = 10;
        }
        $total -= $discount;

        // Step 4: Loyalty Points (Primitive obsession + Hardcoded SQL)
        $loyaltyPoints = $this->legacyRepository->getUserLoyaltyPoints($request['userId']);
        if ($loyaltyPoints > 50 && $total > 200) {
            $total -= 20; // Apply loyalty discount if user has more than 50 points
        } elseif ($loyaltyPoints < 20) {
            $total += 5; // Charge extra for "inactivity fee"
        }

        // TODO: Log loyalty program usage - ask business?

        // Step 5: Final Order Invariant Check
        if ($total < 0) {
            // This should never happen, but just in case.
            return [
                'status' => 'error',
                'message' => "Invalid order total!",
            ];
        }

        // Step 6: Insert Order to Database
        $this->legacyRepository->createNewOrder($request, $total, $discount);

        // Hardcoded logic for future analytics
        if (isset($request['promoCode'])) {
            $this->legacyRepository->addPromoCodeUsage($request);
        }

        // Step 7: Misleading Logging
        $logEntry = json_encode([
            'userId' => $request['userId'],
            'total' => $total,
            'items' => $request['items'],
            'promoCode' => $request['promoCode'] ?? null,
            'loyaltyPoints' => $loyaltyPoints
        ]);
        file_put_contents('/var/log/order_process.log', $logEntry . PHP_EOL, FILE_APPEND);

        return [
            'success' => true,
        ];
    }


}
