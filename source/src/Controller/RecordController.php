<?php declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Repository\UserDictionaryRecordRepository;
use App\Repository\UserRepository;
use DateTimeImmutable;
use DateTimeZone;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\UserDictionaryRecordEntity;
use App\State\State0;
use App\Service\UserDictionaryRecordService;

class RecordController extends AbstractController
{
    function __construct(
        private UserRepository $userRepository,
        private UserDictionaryRecordRepository $userDictionaryRecordRepository,
        private UserDictionaryRecordService $userDictionaryRecordService,
    ) {}

    #[Route('/records', name: 'route_records_get', methods: ['GET'])]
    public function getRecords(Request $request)
    {
        $userId = $request->getSession()->get('user_id');
        if (is_null($userId)) {
            return $this->redirectToRoute('route_get_login');
        }

        $user = $this->userRepository->getUserById($userId);
        $records = $this->userDictionaryRecordRepository->getRecords($userId);

        return $this->render('Record/list.html.twig', [
            'user' => $user,
            'records' => $records,
        ]);
    }

    #[Route('/record/add', name: 'route_record_add_get', methods: ['GET'])]
    public function getAddRecord(Request $request)
    {
        $userId = $request->getSession()->get('user_id');
        if (is_null($userId)) {
            return $this->redirectToRoute('route_get_login');
        }

        $user = $this->userRepository->getUserById($userId);

        return $this->render('Record/add.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/record/add', name: 'route_record_add_post', methods: ['POST'])]
    public function postAddRecord(Request $request)
    {
        $userId = $request->getSession()->get('user_id');
        if (is_null($userId)) {
            return $this->redirectToRoute('route_get_login');
        }

        $user = $this->userRepository->getUserById($userId);

        $formData = $request->request->all();
        $key = $formData["key"] ?? null;
        if (is_null($key)) {
            // TODO: redirect to route_record_add_get with error
            throw new \Exception("Form error");
        }

        $meaning = $formData["meaning"] ?? null;
        if (is_null($meaning)) {
            // TODO: redirect to route_record_add_get with error
            throw new \Exception("Form error");
        }

        $record = new UserDictionaryRecordEntity(
            -1,
            $userId,
            new State0(),
            $key,
            $meaning,
            new DateTimeImmutable('NOW', new DateTimeZone($user->getTimezone())),
            []
        );
        $this->userDictionaryRecordService->createRecord($record);

        return $this->redirectToRoute('route_record_add_get');
    }

    #[Route('/record/delete/{recordId}', name: 'route_record_delete_get', methods: ['GET'])]
    public function getDeleteRecord(int $recordId, Request $request)
    {
        $userId = $request->getSession()->get('user_id');
        if (is_null($userId)) {
            return $this->redirectToRoute('route_get_login');
        }

        $this->userDictionaryRecordRepository->deleteRecord($userId, $recordId);

        return $this->redirectToRoute('route_records_get');
    }
}