<?php

/**
 * Interface Segregation Principle
 *
 * Good example
 * - The Worker interface is split into two separate interfaces: Workable and Eatble.
 * - The HumanWorker class implements both interfaces.
 * - The RobotWorker class implements only the Workable interface.
 * - The RobotWorker class doesn't have to implement the eat() method.
 */
interface Workable
{
    public function work(): void;
}

interface Eatble
{
    public function eat(): void;
}

final class HumanWorker implements Workable, Eatble
{
    public function work(): void {}
    public function eat(): void {}
}

final class RobotWorker implements Workable
{
    public function work(): void {}
}
