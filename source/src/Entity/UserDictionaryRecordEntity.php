<?php declare(strict_types=1);

namespace App\Entity;

use App\State\AbstractState;
use DateTimeInterface;

class UserDictionaryRecordEntity extends DictionaryRecordEntity
{
    public function __construct(
        protected int $recordId,
        private int $userId,
        private AbstractState $state,
        protected string $key,
        protected ?string $meaning = null,
        private DateTimeInterface $due,
        protected array $links = [],
    ) {
        parent::__construct($recordId, $key, $meaning, $links);

        $this->transitionTo($state);
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

    public function getDue(): DateTimeInterface
    {
        return $this->due;
    }
}