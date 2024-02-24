<?php declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
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

    #[Route('/record', name: 'route_record_get', methods: ['GET'])]
    public function getRecord(Request $request): Response
    {
        $session = $request->getSession();
        $userId = $session->get('user_id');
        if (is_null($userId)) {
            return $this->redirectToRoute('route_get_login');
        }

        $user = $this->userRepository->getUserById($userId);
        $record = $this->recordRepository->findRecord($user->getId());
        if (is_null($record)) {
            return $this->render('Record/no-records.html.twig', [
                'user' => $user,
            ]);
        }

        return $this->render('Record/index.html.twig', [
            'user' => $user,
            "record" => $record,
        ]);
    }

    #[Route('/state/{recordId}/forward', name: 'route_state_forward_get', methods: ['GET'])]
    public function moveForward(Request $request, int $recordId)
    {
        $session = $request->getSession();
        $userId = $session->get('user_id');
        if (is_null($userId)) {
            return $this->redirectToRoute('route_get_login');
        }

        $user = $this->userRepository->getUserById($userId);
        $record = $this->recordRepository->getByUserAndRecordId($userId, $recordId);
        $record->getState()->next();
        $this->recordRepository->updateState($user, $record);

        return $this->redirectToRoute("route_record_get");
    }

    #[Route('/state/{recordId}/backward', name: 'route_state_backward_get', methods: ['GET'])]
    public function moveBackward(Request $request, int $recordId)
    {
        $session = $request->getSession();
        $userId = $session->get('user_id');
        if (is_null($userId)) {
            return $this->redirectToRoute('route_get_login');
        }

        $user = $this->userRepository->getUserById($userId);
        $record = $this->recordRepository->getByUserAndRecordId($userId, $recordId);
        $record->getState()->rollback();
        $this->recordRepository->updateState($user, $record);

        return $this->redirectToRoute("route_record_get");
    }
}