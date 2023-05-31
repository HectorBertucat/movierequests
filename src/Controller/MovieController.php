<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\MovieRepository;

class MovieController extends AbstractController
{
    #[Route('/movies', name: 'app_movie')]
    public function index(Request $request, MovieRepository $movieRepository): Response
    {
        $offset = max(0, $request->query->getInt('offset', 0));
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
}
