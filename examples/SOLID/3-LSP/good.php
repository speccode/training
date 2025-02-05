<?php

/**
 * Liskov Substitution Principle
 *
 * Good example
 * - Pungiun don't have to fly
 * - You can pass only flyable birds to letBirdFly function
 */
abstract class Bird
{
    public function eat(): void
    {
        // common
    }
}

interface Flyable
{
    public function fly(): void;
}

final class Duck extends Bird implements Flyable
{
    public function fly(): void
    {
        echo 'Flying...';
    }
}

final class Punguin extends Bird
{
    // no fly method
}

function letBirdFly(Flyable $bird): void
{
    $bird->fly();
}
