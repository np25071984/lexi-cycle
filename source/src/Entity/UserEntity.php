<?php declare(strict_types=1);

namespace App\Entity;

class UserEntity
{
    public function __construct(
        private int $id,
        private string $email, // TODO: valueObject
        private string $password,
        private ?string $firstname,
        private ?string $lastname,
        private string $timezone,
    ) {}

    public function getId(): int
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @return string hashed password
     * Do we want to have this member in the entity?
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function getFirstName(): ?string
    {
        return $this->firstname;
    }

    public function getLastName(): ?string
    {
        return $this->lastname;
    }

    public function getTimezone(): string
    {
        return $this->timezone;
    }
}