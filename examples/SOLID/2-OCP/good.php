<?php

/**
 * Open/Closed Principle
 *
 * Good example
 * - each payment method is a separate class
 * - PaymentProcessor class is closed for modification but open for extension
 * - new payment methods can be added without modifying the PaymentProcessor class
 */

interface PaymentMethod
{
    public function pay(int $amount): string;
}

final readonly class CreditCardPayment implements PaymentMethod
{
    public function __construct(
        private string $cardNumber,
        private string $expiryDate,
        private string $cvv,
    )
    {
    }

    public function pay(int $amount): string
    {
        // Simulate processing a credit card payment.
        return "Processed credit card payment of \${$amount} using card {$this->cardNumber}.";
    }
}

final readonly class PaypalPayment implements PaymentMethod
{
    public function __construct(private string $email)
    {
    }

    public function pay(int $amount): string
    {
        // Simulate processing a PayPal payment.
        return "Processed PayPal payment of \${$amount} for account {$this->email}.";
    }
}

final readonly class PaymentProcessor
{
    public function processPayment(PaymentMethod $paymentMethod, int $amount): string
    {
        return $paymentMethod->pay($amount);
    }
}

$creditCardPayment = new CreditCardPayment('4111111111111111', '12/2025', '123');
$paypalPayment = new PaypalPayment('customer@example.com');

$processor = new PaymentProcessor();
$processor->processPayment($creditCardPayment, 100);
$processor->processPayment($paypalPayment, 150);
