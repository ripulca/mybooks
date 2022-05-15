<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/')]
class IndexController extends AbstractController
{
    #[Route('/', name: 'app_index', methods: ['GET'])]
    public function index(Request $request, UserRepository $userRepository): Response
    {
        $session = $request->getSession();
        if (!$session->isStarted()) {
            $session->start();
        }
        $email = $session->get(Security::LAST_USERNAME) ?? null;
        $user=$userRepository->findOneByEmail($email);
        return $this->render('index.html.twig', [
            'user'=>$user
        ]);
    }
}
