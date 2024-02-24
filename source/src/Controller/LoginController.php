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
        $userId = $request->getSession()->get('user_id');
        if (!is_null($userId)) {
            return $this->redirectToRoute('route_home_get');
        }

        return $this->render('Login/index.html.twig');
    }

    #[Route('/logout', name: 'route_get_logout', methods: ['GET'])]
    public function getLogout(Request $request): Response
    {
        $request->getSession()->remove('user_id');
        return $this->redirectToRoute('route_get_login');
    }

    #[Route('/login', name: 'route_post_login', methods: ['POST'])]
    public function postLogin(Request $request): Response
    {
        $formData = $request->request->all();
        $email = $formData["email"] ?? null;
        $user = $this->userRepository->findUserByEmail($email);
        if (is_null($user)) {
            // TODO: redirect to route_get_login with error
            throw new \Exception("Auth error");
        }

        $password = $formData["password"] ?? null;
        if ($user->getPassword() !== $password) {
            // TODO: redirect to route_get_login with error
            throw new \Exception("Auth error");
        }

        $session = $request->getSession();
        $session->set('user_id', $user->getId());

        return $this->redirectToRoute('route_home_get');
    }
}