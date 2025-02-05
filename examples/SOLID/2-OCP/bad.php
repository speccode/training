<?php

/**
 * Open/Closed Principle
 *
 * Bad example
 * - conditional logic to determine how to process each type of payment
 * - if a new payment type is added, the class must be modified
 */

final class PaymentProcessor {
    public function processPayment(array $paymentData): void
    {
        if ('credit_card' === $paymentData['type']) {
            return; // credit card magic
        } elseif ('paypal' === $paymentData['type']) {
            return; // PayPal magic
        }

        throw new Exception('Unknown payment method');
    }
}
