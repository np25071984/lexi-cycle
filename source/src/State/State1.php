<?php declare(strict_types=1);

namespace App\State;

class State1 extends AbstractState
{
    protected const DELAY_DAYS = 1;
    protected const STATE_ID = 'state_1';

    public function next(): void
    {
        $nextState = new State7($this->record);
        $this->record->transitionTo($nextState);
    }

    public function rollback(): void
    {
        return;
    }


}