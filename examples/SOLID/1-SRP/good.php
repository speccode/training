<?php

/**
 * Single Responsibility Principle
 *
 * Good example
 * Service now has only one responsibility - orchestrate appointment booking process.
 */

final readonly class AppointmentService
{
    public function __construct(
        private PatientValidator $validator,
        private AppointmentFactory $factory,
        private AppointmentRepository $repository,
        private ConfirmationSender $sender,
    ) {
    }

    public function bookAppointment(array $patientData, DateTimeImmutable $date): void
    {
        $this->validator->validate($patientData);
        $appointment = $this->factory->create($patientData, $date);
        $this->repository->save($appointment);
        $this->sender->send($appointment['email'], $appointment);
    }
}

