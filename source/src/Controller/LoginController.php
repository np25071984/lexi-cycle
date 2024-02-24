<?php declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Repository\UserRepository;

class LoginController extends AbstractController
{
    function __construct(
        private UserRepository $userRepository,
    ) {}

    #[Route('/login', name: 'route_get_login', methods: ['GET'])]
    public function getLogin(Request $request): Response
    {
        $session = $request->getSession();
        $userId = $session->get('user_id');
        if (!is_null($userId)) {
            return $this->redirectToRoute('route_record_get');
        }

        return $this->render('Login/index.html.twig');
    }

    #[Route('/login', name: 'route_post_login', methods: ['POST'])]
    public function postLogin(Request $request): Response
    {
        $formData = $request->request->all();
        $email = $formData["email"] ?? null;
        $user = $this->userRepository->findUserByEmail($email);
        if (is_null($user)) {
            // TODO: error
            throw new \Exception("Auth error");
        }

        $password = $formData["password"] ?? null;
        if ($user->getPassword() !== $password) {
            // TODO: error
            throw new \Exception("Auth error");
        }

        $session = $request->getSession();
        $session->set('user_id', $user->getId());

        return $this->redirectToRoute('route_record_get');
    }
}