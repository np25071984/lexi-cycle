<?php declare(strict_types=1);

namespace App\Entity;

class DictionaryRecordEntity
{
    public function __construct(
        protected int $recordId,
        protected string $key,
        protected ?string $meaning = null,
        protected array $links = [],
    ) {
    }

    public function getRecordId(): int
    {
        return $this->recordId;
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