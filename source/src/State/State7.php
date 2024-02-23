<?php declare(strict_types=1);

namespace App\State;

class State7 extends AbstractState
{
    protected const DELAY_DAYS = 7;
    protected const STATE_ID = 'state_7';

    public function next(): void
    {
        $state30 = new State30($this->record);
        $this->record->transitionTo($state30);
    }

    public function rollback(): void
    {
        $state1 = new State1($this->record);
        $this->record->transitionTo($state1);
    }


}