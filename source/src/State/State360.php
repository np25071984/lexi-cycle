<?php declare(strict_types=1);

namespace App\State;

class State360 extends AbstractState
{
    protected const DELAY_DAYS = 360;
    protected const STATE_ID = 'state_360';

    public function next(): void
    {
        return;
    }

    public function rollback(): void
    {
        $state1 = new State1($this->record);
        $this->record->transitionTo($state1);
    }


}