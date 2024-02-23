<?php declare(strict_types=1);

namespace App\Entity;

use App\State\AbstractState;

class RecordEntity
{
    public function __construct(
        private int $recordId,
        private int $userId,
        private AbstractState $state,
        private string $key,
        private ?string $meaning = null,
        private array $links = [],
    ) {
        $this->transitionTo($state);
    }

    public function getRecordId(): int
    {
        return $this->recordId;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getState(): AbstractState
    {
        return $this->state;
    }

    public function transitionTo(AbstractState $state): void
    {
        $this->state = $state;
        $this->state->setRecord($this);
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