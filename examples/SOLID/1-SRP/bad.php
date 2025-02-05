<?php

/**
 * Single Responsibility Principle
 *
 * Bad example
 *
 * Create Appointment is
 * - validating data
 * - creating
 * - saving it
 * - sending confirmation
 */

final class AppointmentService
{
    public function createAppointment(array $patientData, DateTimeImmutable $date): void
    {
        if (empty($patientData['name']) || empty($patientData['email'])) {
            throw new InvalidArgumentException('Invalid data.');
        }

        $appointment = [
            'patient' => $patientData,
            'date' => $date,
            'confirmed' => false,
        ];

        $this->saveAppointment($appointment);
        $this->sendConfirmation($patientData['email'], $appointment);
    }

    private function saveAppointment(array $appointment): void
    {
        //saving appointment into DB
    }

    private function sendConfirmation(string $email, array $appointment): void
    {
        //sending email confirmation
    }
}
