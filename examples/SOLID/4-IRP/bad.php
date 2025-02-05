<?php

/**
 * Interface Segregation Principle
 *
 * Bad example
 * - The RobotWorker class has to implement the eat() method, even though it doesn't need it.
 */

interface Worker
{
    public function work(): void;
    public function eat(): void;
}

final class HumanWorker implements Worker
{
    public function work(): void {}
    public function eat(): void {}
}

final class RobotWorker implements Worker
{
    public function work(): void {}
    public function eat(): void
    {
        throw new Exception('Robots can\'t eat');
    }
}
