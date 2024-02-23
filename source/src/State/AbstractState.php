<?php declare(strict_types=1);

namespace App\State;

use App\Entity\RecordEntity;

abstract class AbstractState
{
    protected RecordEntity $record;
    protected const DELAY_DAYS = 0;
    protected const STATE_ID = 'state_0';

    public function setRecord(RecordEntity $record): void {
        $this->record = $record;
    }

    public function getDelay(): int
    {
        return static::DELAY_DAYS;
    }

    public function getId(): string
    {
        return static::STATE_ID;
    }

    abstract function next(): void;
    abstract function rollback(): void;
}