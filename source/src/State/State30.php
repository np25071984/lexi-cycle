<?php declare(strict_types=1);

namespace App\State;

class State30 extends AbstractState
{
    protected const DELAY_DAYS = 30;
    protected const STATE_ID = 'state_30';

    public function next(): void
    {
        $state90 = new State90($this->record);
        $this->record->transitionTo($state90);
    }

    public function rollback(): void
    {
        $state1 = new State1($this->record);
        $this->record->transitionTo($state1);
    }


}