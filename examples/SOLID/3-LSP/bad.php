<?php

/**
 * Liskov Substitution Principle
 *
 * Bad example
 * - A child class should never break the parent class type definitions
 * - Punguin can't fly, but it MUST have fly method
 * - If you pass Punguin to letBirdFly function, it will throw an exception
 */
class Bird
{
    public function eat(): void
    {

    }

    public function fly(): void
    {

    }
}

final class Duck extends Bird
{
    public function fly(): void
    {
        echo 'Flying...';
    }
}

final class Punguin extends Bird
{
    public function fly(): void
    {
        throw new Exception('I can\'t fly');
    }
}

function letBirdFly(Bird $bird): void
{
    $bird->fly();
}
