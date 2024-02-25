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
        $urlQuery = $request->query->all();
        $currentPage = $urlQuery["page"] ?? 1;

        $userId = $request->getSession()->get('user_id');
        if (is_null($userId)) {
            return $this->redirectToRoute('route_get_login');
        }
        $user = $this->userRepository->getUserById($userId);

        $perPageCount = 2;
        $count = $this->userDictionaryRecordRepository->getRecordsCount($userId);
        $records = $this->userDictionaryRecordRepository->getRecords(
            $userId,
            ($currentPage - 1) * $perPageCount,
            $perPageCount
        );
        $maxPage = ceil($count / $perPageCount);

        return $this->render('Record/list.html.twig', [
            'user' => $user,
            'records' => $records,
            'current_page' => $currentPage,
            'max_page' => $maxPage,
        ]);
    }

    #[Route('/records/{recordId}', name: 'route_record_get', methods: ['GET'])]
    public function getRecord(Request $request, int $recordId)
    {
        $userId = $request->getSession()->get('user_id');
        if (is_null($userId)) {
            return $this->redirectToRoute('route_get_login');
        }

        $user = $this->userRepository->getUserById($userId);
        $record = $this->userDictionaryRecordRepository->findByUserAndRecordId($userId, $recordId);
        if (is_null($record)) {
            throw new \Exception("Record wasn't found");
        }

        return $this->render('Home/index.html.twig', [
            "user" => $user,
            "record" => $record,
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

    #[Route('/record/{recordId}/edit', name: 'route_record_edit_get', methods: ['GET'])]
    public function getEditRecord(Request $request, int $recordId)
    {
        $userId = $request->getSession()->get('user_id');
        if (is_null($userId)) {
            return $this->redirectToRoute('route_get_login');
        }
        $user = $this->userRepository->getUserById($userId);

        $record = $this->userDictionaryRecordRepository->findByUserAndRecordId($userId, $recordId);
        if (is_null($record)) {
            throw new \Exception("Record wasn't found");
        }

        return $this->render('Record/edit.html.twig', [
            'user' => $user,
            'record' => $record,
        ]);
    }

    #[Route('/record/{recordId}/edit', name: 'route_record_edit_post', methods: ['POST'])]
    public function postEditRecord(Request $request, int $recordId)
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

        $links = [];
        if (isset($formData["title"]) && isset($formData["url"])) {
            foreach ($formData["title"] as $i => $title) {
                $links[] = [
                    "title" => $title,
                    "url" => $formData["url"][$i],
                ];
            }
        }

        $record = new UserDictionaryRecordEntity(
            $recordId,
            $userId,
            new State0(), // let's consider an updated Record as a completely new one
            $key,
            $meaning,
            new DateTimeImmutable('NOW', new DateTimeZone($user->getTimezone())),
            $links
        );
        $this->userDictionaryRecordService->updateRecord($record);

        return $this->redirectToRoute('route_records_get');
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

        $links = [];
        if (isset($formData["title"]) && isset($formData["url"])) {
            foreach ($formData["title"] as $i => $title) {
                $links[] = [
                    "title" => $title,
                    "url" => $formData["url"][$i],
                ];
            }
        }

        $record = new UserDictionaryRecordEntity(
            -1,
            $userId,
            new State0(),
            $key,
            $meaning,
            new DateTimeImmutable('NOW', new DateTimeZone($user->getTimezone())),
            $links
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