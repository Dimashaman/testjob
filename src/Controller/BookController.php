<?php

namespace App\Controller;

use Exception;
use App\Entity\Book;
use App\Form\BookType;
use App\Dto\BookFilterDto;
use Doctrine\ORM\EntityManager;
use App\Repository\BookRepository;
use App\Service\FileUploadService;
use App\Repository\AuthorRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/book")
 */
class BookController extends AbstractController
{
    /**
     * @Route("/", name="book_index", methods={"GET"})
     */
    public function index(BookRepository $bookRepository, AuthorRepository $authorRepository): Response
    {
        return $this->render('book/index.html.twig', [
            'books' => $bookRepository->findAll(),
            'authors' => $authorRepository->findAll()
        ]);
    }

    /**
     * @Route("/filter", name="book_filter", methods={"GET"})
     */
    public function filter(Request $request, BookRepository $bookRepository, AuthorRepository $authorRepository, BookFilterDto $bookFilterDTO, ValidatorInterface $validator) : Response
    {
        $bookFilterDTO = $bookFilterDTO->createFromQueryParams($request->query->all());
        $errors = $validator->validate($bookFilterDTO);
        
        if (count($errors) > 0) {
            return $this->render('book/index.html.twig', ['errors' => $errors]);
        }

        $books = $bookRepository->applyFilters($bookFilterDTO);
        
        return $this->render('book/index.html.twig', [
            'books' => $books,
            'authors' => $authorRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="book_new", methods={"GET","POST"})
     */
    public function new(Request $request, FileUploadService $fileUploadService): Response
    {
        $book = new Book();
        $form = $this->createForm(BookType::class, $book);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $coverImageFile = $form->get('cover')->getData();
            if ($coverImageFile) {
                $book->setCover($fileUploadService->uploadAndReturnPath($coverImageFile));
            }
            /**
             * @var EntityManager $em
             */
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            try {
                $em->persist($book);
                $em->flush();
                $em->getConnection()->commit();
            } catch (Exception $e) {
                $em->getConnection()->rollBack();
                throw $e;
            }

            return $this->redirectToRoute('book_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('book/new.html.twig', [
            'book' => $book,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="book_show", methods={"GET"})
     */
    public function show(Book $book): Response
    {
        return $this->render('book/show.html.twig', [
            'book' => $book,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="book_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Book $book, FileUploadService $fileUploadService): Response
    {
        $form = $this->createForm(BookType::class, $book);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $coverImageFile = $form->get('cover')->getData();
            if ($coverImageFile) {
                $book->setCover($fileUploadService->uploadAndReturnPath($coverImageFile));
            }
            /**
             * @var EntityManager $em
             */
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            try {
                $em->flush();
                $em->getConnection()->commit();
            } catch (Exception $e) {
                $em->getConnection()->rollBack();
                throw $e;
            }
            
            return $this->redirectToRoute('book_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('book/edit.html.twig', [
            'book' => $book,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/inlineEdit", name="book_inline_edit", methods={"GET","POST"})
     */
    public function inlineEdit(Request $request, Book $book) : Response
    {
        $form = $this->createForm(BookType::class, $book);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            return $this->render('book/_inline.html.twig', [
                'book' => $book
            ]);
        }

        return $this->render('book/_inline_form.html.twig', [
            'book' => $book,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="book_delete", methods={"POST"})
     */
    public function delete(Request $request, Book $book): Response
    {
        if ($this->isCsrfTokenValid('delete'.$book->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($book);
            $entityManager->flush();
        }

        return $this->redirectToRoute('book_index', [], Response::HTTP_SEE_OTHER);
    }
}
