<?php

namespace App\Controller;

use App\Entity\Book;
use App\Form\BookType;
use App\Repository\BookRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/book')]
class BookController extends AbstractController
{
    #[Route('/', name: 'app_book_index', methods: ['GET'])]
    public function index(Request $request, BookRepository $bookRepository, UserRepository $userRepository): Response
    {
        $session = $request->getSession();
        if (!$session->isStarted()) {
            $session->start();
        }
        $email = $session->get(Security::LAST_USERNAME) ?? null;
        $user=$userRepository->findOneByEmail($email);
        $user_id=$user->getId();
        return $this->render('book/index.html.twig', [
            'books' => $bookRepository->findBy(
                ['user_id' => $user_id],
            ),
            'user'=>$user
        ]);
    }

    #[Route('/new', name: 'app_book_new', methods: ['GET', 'POST'])]
    public function new(Request $request, BookRepository $bookRepository): Response
    {
        $book = new Book();
        $form = $this->createForm(BookType::class, $book);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $bookRepository->add($book, true);

            return $this->redirectToRoute('app_book_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('book/new.html.twig', [
            'book' => $book,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_book_show', methods: ['GET'])]
    public function show(Request $request, Book $book, UserRepository $userRepository): Response
    {
        $session = $request->getSession();
        if (!$session->isStarted()) {
            $session->start();
        }
        $email = $session->get(Security::LAST_USERNAME) ?? null;
        $user=$userRepository->findOneByEmail($email);
        return $this->render('book/show.html.twig', [
            'book' => $book,
            'user'=>$user
        ]);
    }

    #[Route('/{id}/edit', name: 'app_book_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Book $book, BookRepository $bookRepository, UserRepository $userRepository): Response
    {
        $session = $request->getSession();
        if (!$session->isStarted()) {
            $session->start();
        }
        $email = $session->get(Security::LAST_USERNAME) ?? null;
        $user=$userRepository->findOneByEmail($email);
        $form = $this->createForm(BookType::class, $book);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $bookRepository->add($book, true);

            return $this->redirectToRoute('app_book_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('book/edit.html.twig', [
            'book' => $book,
            'form' => $form,
            'user'=>$user
        ]);
    }

    #[Route('/{id}', name: 'app_book_delete', methods: ['POST'])]
    public function delete(Request $request, Book $book, BookRepository $bookRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$book->getId(), $request->request->get('_token'))) {
            $bookRepository->remove($book, true);
        }

        return $this->redirectToRoute('app_book_index', [], Response::HTTP_SEE_OTHER);
    }
}
