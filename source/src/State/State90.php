<?php declare(strict_types=1);

namespace App\State;

class State90 extends AbstractState
{
    protected const DELAY_DAYS = 90;
    protected const STATE_ID = 'state_90';

    public function next(): void
    {
        $state360 = new State360($this->record);
        $this->record->transitionTo($state360);
    }

    public function rollback(): void
    {
        $state1 = new State1($this->record);
        $this->record->transitionTo($state1);
    }


}