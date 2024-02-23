<?php declare(strict_types=1);

namespace  App\State;

class StateFactory
{
    public function getState(string $stateId): AbstractState
    {
        switch ($stateId) {
            case "state_0":
                $state = new State0();
                break;
            case "state_1":
                $state = new State1();
                break;
            case "state_7":
                $state = new State7();
                break;
            case "state_30":
                $state = new State30();
                break;
            case "state_90":
                $state = new State90();
                break;
            case "state_360":
                $state = new State360();
                break;
            default:
                throw new \Exception("Unknown State value {$stateId}");
        }

        return $state;
    }
}