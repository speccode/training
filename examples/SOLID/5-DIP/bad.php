<?php

/**
 * Dependency Inversion Principle
 *
 * Bad example
 * - High-level modules should not depend on low-level modules. Both should depend on abstractions.
 * - Abstractions should not depend on details. Details should depend on abstractions.
 * - The PasswordReminder class depends on the MySQLConnection class.
 */

final readonly class PasswordReminder
{
    public function __construct(private MySQLConnection $dbConnection)
    {
    }
}
