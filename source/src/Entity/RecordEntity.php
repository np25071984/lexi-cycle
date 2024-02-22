<?php

namespace App\Entity;

readonly class RecordEntity
{
    public function __construct(
        private int $recordId,
        private int $userId,
        private string $key,
        private ?string $meaning = null,
        private array $links = [],
    ) {}

    public function getRecordId(): int
    {
        return $this->recordId;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getMeaning(): ?string
    {
        return $this->meaning;
    }

    public function getLinks(): array
    {
        return $this->links;
    }
}