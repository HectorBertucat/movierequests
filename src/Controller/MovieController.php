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

        return $this->render('movie/index.html.twig', [
            'movies' => $paginator,
            'previous' => $offset - MovieRepository::PAGINATOR_PER_PAGE,
            'next' => min(count($movies), $offset + MovieRepository::PAGINATOR_PER_PAGE),
        ]);
    }
}
