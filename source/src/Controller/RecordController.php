<?php declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Repository\RecordRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;

class RecordController extends AbstractController
{
    function __construct(
        private UserRepository $userRepository,
        private RecordRepository $recordRepository,
    ) {}

    #[Route('/records', name: 'route_records_get', methods: ['GET'])]
    public function getRecords(Request $request)
    {
        $userId = $request->getSession()->get('user_id');
        if (is_null($userId)) {
            return $this->redirectToRoute('route_get_login');
        }

        $user = $this->userRepository->getUserById($userId);
        $records = $this->recordRepository->getRecords($userId);

        return $this->render('Record/list.html.twig', [
            'user' => $user,
            'records' => $records,
        ]);
    }

    #[Route('/state/{recordId}/forward', name: 'route_state_forward_get', methods: ['GET'])]
    public function moveForward(Request $request, int $recordId)
    {
        $userId = $request->getSession()->get('user_id');
        if (is_null($userId)) {
            return $this->redirectToRoute('route_get_login');
        }

        $user = $this->userRepository->getUserById($userId);
        $record = $this->recordRepository->getByUserAndRecordId($userId, $recordId);
        $record->getState()->next();
        $this->recordRepository->updateState($user, $record);

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
        $record = $this->recordRepository->getByUserAndRecordId($userId, $recordId);
        $record->getState()->rollback();
        $this->recordRepository->updateState($user, $record);

        return $this->redirectToRoute("route_home_get");
    }
}