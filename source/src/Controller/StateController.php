<?php declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Repository\UserDictionaryRecordRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;

class StateController extends AbstractController
{
    function __construct(
        private UserRepository $userRepository,
        private UserDictionaryRecordRepository $recordRepository,
    ) {}

    #[Route('/state/{recordId}/forward', name: 'route_state_forward_get', methods: ['GET'])]
    public function moveForward(Request $request, int $recordId)
    {
        $userId = $request->getSession()->get('user_id');
        if (is_null($userId)) {
            return $this->redirectToRoute('route_get_login');
        }

        $user = $this->userRepository->getUserById($userId);
        $record = $this->recordRepository->findByUserAndRecordId($userId, $recordId);
        if ($record) {
            $record->getState()->next();
            $this->recordRepository->updateState($user, $record);
        }

        return $this->redirectToRoute("route_home_get");
    }

    #[Route('/state/{recordId}/backward', name: 'route_state_backward_get', methods: ['GET'])]
    public function moveBackward(Request $request, int $recordId)
    {
        $userId = $request->getSession()->get('user_id');
        if (is_null($userId)) {
            return $this->redirectToRoute('route_get_login');
        }

        $user = $this->userRepository->getUserById($userId);
        $record = $this->recordRepository->findByUserAndRecordId($userId, $recordId);
        if ($record) {
            $record->getState()->rollback();
            $this->recordRepository->updateState($user, $record);
        }

        return $this->redirectToRoute("route_home_get");
    }
}