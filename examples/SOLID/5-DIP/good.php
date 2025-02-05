<?php

/**
 * Dependency Inversion Principle
 *
 * Good example
 * - PasswordReminder class depends on the DbConnection interface.
 * - Dependency inversion is achieved by injecting the DbConnection interface into the PasswordReminder class.
 */
interface DbConnection
{
    public function connect(): void;
}

final class MySqlConnection implements DbConnection
{
    public function connect(): void
    {
        echo 'Connected to MySQL';
    }
}

final readonly class PasswordReminder
{
    public function __construct(private DbConnection $dbConnection)
    {
    }
}
