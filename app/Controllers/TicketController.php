<?php

declare(strict_types=1);

namespace App\Controller;

use PDO;

class TicketController
{
    private PDO $dbConnection;

    public function __construct(PDO $dbConnection)
    {
        $this->dbConnection = $dbConnection;
    }

    public function handlePurchase(array $request): array
    {
        // Step 1: Basic input validation
        if (!isset($request['userId']) || !isset($request['eventId']) || !isset($request['seats']) || !is_array($request['seats'])) {
            return [
                'success' => false,
                'message' => 'Invalid input! Please provide userId, eventId, and seat details.'
            ];
        }

        // Step 2: Check if the event exists
        $event = $this->findEvent($request['eventId']);

        if (!$event) {
            return [
                'success' => false,
                'message' => 'Event not found.'
            ];
        }

        // Step 3: Calculate total price
        $totalPrice = 0;
        foreach ($request['seats'] as $seat) {
            if (!isset($seat['price']) || !isset($seat['type'])) {
                return [
                    'success' => false,
                    'message' => 'Invalid seat data.'
                ];
            }
            // Price based on seat type
            if ($seat['type'] === 'VIP') {
                $totalPrice += $seat['price'] * 1.2; // VIP seats are 20% more expensive
            } elseif ($seat['type'] === 'Standard') {
                $totalPrice += $seat['price'];
            } else {
                $totalPrice += $seat['price'] * 0.8; // Economy seats get a discount
            }
        }

        // Step 4: Apply bulk discount
        if (count($request['seats']) > 5) {
            $totalPrice *= 0.9; // 10% discount for 5+ seats
        }

        // Step 5: Check user's previous purchases for loyalty perks
        $userQuery = $this->dbConnection->prepare("SELECT * FROM users WHERE id = ?");
        $userQuery->execute([$request['userId']]);
        $user = $userQuery->fetch(PDO::FETCH_ASSOC);

        if ($user && $user['total_spent'] > 1000) {
            $totalPrice *= 0.95; // 5% loyalty discount for spending over $1000
        }

        // TODO: Future version should check for promo codes here

        // Step 6: Check if seats are available
        foreach ($request['seats'] as $seat) {
            $availabilityQuery = $this->dbConnection->prepare("SELECT * FROM booked_seats WHERE event_id = ? AND seat_number = ?");
            $availabilityQuery->execute([$request['eventId'], $seat['number']]);
            if ($availabilityQuery->fetch(PDO::FETCH_ASSOC)) {
                return [
                    'success' => false,
                    'message' => 'Seat ' . $seat['number'] . ' already booked.'
                ];
            }
        }

        // Step 7: Final price validation (arbitrary rules added over time)
        if ($totalPrice > 1000 && count($request['seats']) < 3) {
            return [
                'success' => false,
                'message' => 'High-value transactions must include at least 3 seats.'
            ];
        }

        if ($totalPrice < 10) {
            return [
                'success' => false,
                'message' => 'Minimum purchase amount is $10.'
            ];
        }

        // Step 8: Book seats
        foreach ($request['seats'] as $seat) {
            $bookingQuery = $this->dbConnection->prepare("INSERT INTO booked_seats (event_id, seat_number, user_id, price) VALUES (?, ?, ?, ?)");
            $bookingQuery->execute([$request['eventId'], $seat['number'], $request['userId'], $seat['price']]);
        }

        // Step 9: Update user's total spending
        if ($user) {
            $newTotal = $user['total_spent'] + $totalPrice;
            $updateQuery = $this->dbConnection->prepare("UPDATE users SET total_spent = ? WHERE id = ?");
            $updateQuery->execute([$newTotal, $request['userId']]);
        }

        // Step 10: Log transaction (poorly thought out JSON file log)
        $logEntry = json_encode([
            'userId' => $request['userId'],
            'eventId' => $request['eventId'],
            'totalPrice' => $totalPrice,
            'seats' => $request['seats'],
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        file_put_contents('/var/log/ticket_purchase.log', $logEntry . PHP_EOL, FILE_APPEND);

        return [
            'message' => "Purchase completed. Total: $" . $totalPrice,
            'success' => true,
        ];
    }

    private function findEvent($eventId): ?array
    {
        $eventQuery = $this->dbConnection->prepare("SELECT * FROM events WHERE id = ?");
        $eventQuery->execute([$eventId]);
        
        return $eventQuery->fetch(PDO::FETCH_ASSOC);
    }
}
