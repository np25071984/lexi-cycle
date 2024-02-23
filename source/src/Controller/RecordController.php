<?php declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Repository\RecordRepository;
use App\Repository\UserRepository;

class RecordController extends AbstractController
{
    function __construct(
        private UserRepository $userRepository,
        private RecordRepository $recordRepository,
    ) {}

    #[Route('/{userId}', name: 'show_record', methods: ['GET'])]
    public function showRecord(int $userId): Response
    {
        $user = $this->userRepository->getUserById($userId);
        $record = $this->recordRepository->findRecord($user->getId());
        if (is_null($record)) {
            return $this->render('no-records.html.twig', [
                'user' => $user,
            ]);
        }

        return $this->render('index.html.twig', [
            'user' => $user,
            "record" => $record,
        ]);
    }

    #[Route('/{userId}/state/{recordId}/forward', name: 'move_record_forward', methods: ['GET'])]
    public function moveForward(int $userId, int $recordId)
    {
        $user = $this->userRepository->getUserById($userId);
        $record = $this->recordRepository->getByUserAndRecordId($userId, $recordId);
        $record->getState()->next();
        $this->recordRepository->updateState($user, $record);

        return $this->redirectToRoute("show_record", ['userId' => $userId]);
    }

    #[Route('/{userId}/state/{recordId}/backward', name: 'move_record_backward', methods: ['GET'])]
    public function moveBackward(int $userId, int $recordId)
    {
        $user = $this->userRepository->getUserById($userId);
        $record = $this->recordRepository->getByUserAndRecordId($userId, $recordId);
        $record->getState()->rollback();
        $this->recordRepository->updateState($user, $record);

        return $this->redirectToRoute("show_record", ['userId' => $userId]);
    }
}