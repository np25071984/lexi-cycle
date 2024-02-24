<?php declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Repository\UserDictionaryRecordRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;

class HomeController extends AbstractController
{
    function __construct(
        private UserRepository $userRepository,
        private UserDictionaryRecordRepository $recordRepository,
    ) {}

    #[Route("/", name: "route_home_get", methods: ["GET"])]
    public function getRecord(Request $request): Response
    {
        $userId = $request->getSession()->get("user_id");
        if (is_null($userId)) {
            return $this->redirectToRoute("route_get_login");
        }

        $user = $this->userRepository->getUserById($userId);
        $record = $this->recordRepository->findRecordToReview($user->getId());
        if (is_null($record)) {
            return $this->render('Home/no-records.html.twig', [
                "user" => $user,
            ]);
        }

        return $this->render('Home/index.html.twig', [
            "user" => $user,
            "record" => $record,
        ]);
    }

}