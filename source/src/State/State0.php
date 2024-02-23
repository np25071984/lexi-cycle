<?php declare(strict_types=1);

namespace App\State;

class State0 extends AbstractState
{
    public function next(): void
    {
        $nextState = new State1($this->record);
        $this->record->transitionTo($nextState);
    }

    public function rollback(): void
    {
        $this->next();
    }
}