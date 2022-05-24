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
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

#[Route('/books')]
class BookController extends AbstractController
{
    #[Route('/', name: 'app_book_index', methods: ['GET'])]
    public function index(Request $request, BookRepository $bookRepository, UserRepository $userRepository, $page=1): Response      //вывод начальной страницы
    {
        $session = $request->getSession();
        $email = $session->get(Security::LAST_USERNAME) ?? null;    //получение почты для вывода на странице
        if($email!=NULL){   //если пользователь авторизован, выводим только его книги
            if (!$session->isStarted()) {
                $session->start();
                $user=$userRepository->findOneByEmail($email);
                $user_id=$user->getId();
                $books= $bookRepository->getAllBooksById($user_id);
                $totalBooksReturned = $books->getIterator()->count();
                $totalBooks = $books->count();
                $maxPages=1;
                if($totalBooks!=0 && $totalBooksReturned!=0){
                    $maxPages = ceil($totalBooks / $totalBooksReturned);
                }
                return $this->render('book/index.html.twig', [
                    'books' => $books,
                    'maxPages' => $page,
                    'user'=>$user,
                    'maxPages' => $maxPages,
                    'thisPage'=>$page,
                ]);
            }
        }
        else{       //иначе выводим все книги
            $books= $bookRepository->getAllBooks();
            $totalBooksReturned = $books->getIterator()->count();
            $totalBooks = $books->count();
            $maxPages=1;
            if($totalBooks!=0 && $totalBooksReturned!=0){
                $maxPages = ceil($totalBooks / $totalBooksReturned);
            }
            return $this->render('book/index.html.twig', [
                'books' => $books,
                'maxPages' => $page,
                'maxPages' => $maxPages,
                'thisPage'=>$page,
            ]);
        }
    }
    
    #[Route('/{page}', name: 'app_book_pages_index', requirements: ['page' => '\d+'], methods: ['GET'])]
    public function page_index(Request $request, BookRepository $bookRepository, UserRepository $userRepository, $page=1): Response     //начальная страница с пагинацией
    {
        $session = $request->getSession();
        $email = $session->get(Security::LAST_USERNAME) ?? null;        
        if($email!=NULL){
            if (!$session->isStarted()) {
                $session->start();
            }
            $user=$userRepository->findOneByEmail($email);
            $user_id=$user->getId();
            $books= $bookRepository->getAllBooksById($user_id, $page);
            $totalBooksReturned = $books->getIterator()->count();
            $totalBooks = $books->count();
            $maxPages=1;
            if($totalBooks!=0 && $totalBooksReturned!=0){
                $maxPages = ceil($totalBooks / $totalBooksReturned);
            }
            return $this->render('book/index.html.twig', [
                'books' =>$books,
                'user'=>$user,
                'maxPages' => $maxPages,
                'thisPage'=>$page,
            ]);
        }
        else{
            $books= $bookRepository->getAllBooks($page);
            $totalBooksReturned = $books->getIterator()->count();
            $totalBooks = $books->count();
            $maxPages=1;
            if($totalBooks!=0 && $totalBooksReturned!=0){
                $maxPages = ceil($totalBooks / $totalBooksReturned);
            }
            return $this->render('book/index.html.twig', [
                'books' =>$books,
                'maxPages' => $maxPages,
                'thisPage'=>$page,
            ]);
        }
    }

    #[Route('/new', name: 'app_book_new', methods: ['GET', 'POST'])]
    public function new(Request $request, BookRepository $bookRepository, UserRepository $userRepository, SluggerInterface $slugger): Response      //создание новой книги
    {
        $session = $request->getSession();
        if (!$session->isStarted()) {
            $session->start();
        }
        $email = $session->get(Security::LAST_USERNAME) ?? null;
        $user=$userRepository->findOneByEmail($email);
        $book = new Book();
        $form = $this->createForm(BookType::class, $book);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $coverFile = $form->get('cover')->getData();
            $bookFile = $form->get('file')->getData();
            if($coverFile && $bookFile) {
                $cover_originalFilename = pathinfo($coverFile->getClientOriginalName(), PATHINFO_FILENAME);
                $originalFilename = pathinfo($bookFile->getClientOriginalName(), PATHINFO_FILENAME);
                $cover_safeFilename = $slugger->slug($cover_originalFilename);
                $safeFilename = $slugger->slug($originalFilename);
                $cover_newFilename = $cover_safeFilename.'-'.uniqid().'.'.$coverFile->guessExtension();
                $newFilename = $safeFilename.'-'.uniqid().'.'.$bookFile->guessExtension();

                try {
                    // var_dump($cover_newFilename);
                    $coverFile->move(
                        $this->getParameter('img_directory'),       //сохранение обложки на сервере
                        $cover_newFilename
                    );
                    $bookFile->move(
                        $this->getParameter('file_directory'),       //сохранение файла книги на сервере
                        $newFilename
                    );
                } catch (FileException $e) {
                    echo 'Error: '.$e->getMessage.'\n';
                }
                $book->setAuthor($form->get('author')->getData());
                $book->setUserId($user);
                $book->setCover('\\resources\\img\\'.$cover_newFilename);
                $book->setFile('\\resources\\files\\'.$newFilename);
                $bookRepository->add($book, true);      //добавление записи
            }

            return $this->redirectToRoute('app_book_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('book/new.html.twig', [
            'book' => $book,
            'form' => $form,
            'user'=>$user
        ]);
    }

    #[Route('/book_obj/{id}', name: 'app_book_show', methods: ['GET'])]
    public function show(Request $request, Book $book, UserRepository $userRepository): Response    //показать одну книгу
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

    #[Route('/book_obj/{id}/edit', name: 'app_book_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Book $book, BookRepository $bookRepository, UserRepository $userRepository): Response    //редактировать книгу
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

    #[Route('/book_obj/{id}', name: 'app_book_delete', methods: ['POST'])]
    public function delete(Request $request, Book $book, BookRepository $bookRepository): Response      //удалить книгу
    {
        if ($this->isCsrfTokenValid('delete'.$book->getId(), $request->request->get('_token'))) {
            $bookRepository->remove($book, true);
        }

        return $this->redirectToRoute('app_book_index', [], Response::HTTP_SEE_OTHER);
    }
}
