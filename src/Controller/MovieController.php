<?php

namespace App\Controller;

use App\Form\MovieImageFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\MovieRepository;

class MovieController extends AbstractController
{
    #[Route('/movies', name: 'app_movie')]
    public function index(Request $request, MovieRepository $movieRepository): Response
    {
        $offset = max(0, $request->query->getInt('offset', 0));

        // if offset is not dividable by PAGE_PER_PAGE, set offset to the previous dividable number
        if ($offset % MovieRepository::PAGINATOR_PER_PAGE !== 0) {
            $offset = $offset - ($offset % MovieRepository::PAGINATOR_PER_PAGE);
        }

        $paginator = $movieRepository->getMoviePaginator($offset);

        $movies = $movieRepository->findAll();

        // get total number of movies
        $totalMovies = count($movies);

        // if paginator is empty, set offset to 0
        if ($offset > $totalMovies - 1) {
            $paginator = $movieRepository->getMoviePaginator(0);
            $offset = 0;
        }

        $currentPage = ($offset / MovieRepository::PAGINATOR_PER_PAGE) + 1;
        $totalPages = ceil($totalMovies / MovieRepository::PAGINATOR_PER_PAGE);

        return $this->render('movie/index.html.twig', [
            'movies' => $paginator,
            'previous' => $offset - MovieRepository::PAGINATOR_PER_PAGE,
            'next' => min(count($movies), $offset + MovieRepository::PAGINATOR_PER_PAGE),
            'currentPage' => $currentPage,
            'totalPages' => $totalPages,
            'nbPerPage' => MovieRepository::PAGINATOR_PER_PAGE,
        ]);
    }

    #[Route('/movies/{id}', name: 'app_movie_show')]
    public function show($id, MovieRepository $movieRepository): Response
    {
        $movie = $movieRepository->find($id);

        // if movie not found, redirect to movie index
        if (!$movie) {
            return $this->redirectToRoute('app_movie');
        }

        return $this->render('movie/show.html.twig', [
            'movie' => $movie,
        ]);
    }

    #[Route('/movies/{id}/edit', name: 'app_movie_edit')]
    public function edit($id, MovieRepository $movieRepository, Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $movie = $movieRepository->find($id);

        // if movie not found, redirect to movie index
        if (!$movie) {
            return $this->redirectToRoute('app_movie');
        }

        $form = $this->createForm(MovieImageFormType::class, $movie);
        $form->handleRequest($request);

        $error = null;

        if ($form->isSubmitted() && $form->isValid()) {
            $moviePoster = $form->get('imageFile')->getData();

            if($moviePoster) {
                $originalFilename = pathinfo($moviePoster->getClientOriginalName(), PATHINFO_FILENAME);
                $newFilename = $movie->getId() .".".$moviePoster->guessExtension();

                try {
                    $moviePoster->move(
                        $this->getParameter('movie_poster_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    $error = $e->getMessage();
                }

                $movie->setHasImage(true);
            }

            $entityManager->persist($movie);
            $entityManager->flush();

            return $this->redirectToRoute('app_movie_show', [
                'id' => $movie->getId(),
                'error' => $error,
            ]);
        }

        return $this->render('movie/edit.html.twig', [
            'addImageForm' => $form->createView(),
            'movie' => $movie,
            'error' => $error
        ]);
    }
}
