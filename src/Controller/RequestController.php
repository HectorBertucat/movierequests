<?php

namespace App\Controller;

use App\Form\RequestFormType;
use App\Repository\MovieRepository;
use App\Repository\RequestRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RequestController extends AbstractController
{
    #[Route('/', name: 'app_request')]
    public function index(Request $request, RequestRepository $requestRepository): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $isAdmin = in_array('ROLE_ADMIN', $this->getUser()->getRoles());
        $latestApprovedRequest = $requestRepository->getLastAcceptedRequests();

        $offset = max(0, $request->query->getInt('offset', 0));

        $requests = $requestRepository->findAll();
        $totalMovies = count($requests);

        if ($isAdmin) {
            // if admin, only get pending requests
            $paginator = $requestRepository->getRequestPaginator($offset, null, 1);

            // if paginator is empty, set offset to 0
            if ($offset > $totalMovies - 1) {
                $paginator = $requestRepository->getRequestPaginator(0);
                $offset = 0;
            }

        } else {
            // else get requests of user
            $paginator = $requestRepository->getRequestPaginator($offset, $this->getUser());

            // if paginator is empty, set offset to 0
            if ($offset > $totalMovies - 1) {
                $paginator = $requestRepository->getRequestPaginator(0);
                $offset = 0;
            }
        }

        return $this->render('request/index.html.twig', [
            'requests'              => $paginator,
            'latestApprovedRequest' => $latestApprovedRequest,
            'previous'              => $offset - RequestRepository::PAGINATOR_PER_PAGE,
            'next'                  => min(count($requests), $offset + RequestRepository::PAGINATOR_PER_PAGE),
        ]);
    }

    #[Route('/request/make', name: 'app_request_make')]
    public function makeRequest(Request $request, EntityManagerInterface $entityManager, MovieRepository $movieRepository): Response
    {
        $requestMovie = new \App\Entity\Request();
        $form = $this->createForm(RequestFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $requestMovie->setMadeBy($this->getUser());
            $requestMovie->setStatus(1);

            $movie = $movieRepository->find($form->get('movie')->getData());

            // if movie already has a request, redirect to request index
            if ($movie->getRequests()->count() > 0) {
                return $this->redirectToRoute('app_request');
            }

            $requestMovie->setMovie($movie);
            $requestMovie->setDateCreated(new \DateTime());

            $entityManager->persist($requestMovie);
            $entityManager->flush();

            return $this->redirectToRoute('app_request_show', ['id' => $requestMovie->getId()]);
        }

        return $this->render('request/make.html.twig', [
            'requestForm' => $form->createView(),
        ]);
    }

    #[Route('/requests/{id}', name: 'app_request_show')]
    public function show($id, RequestRepository $requestRepository): Response
    {
        $request = $requestRepository->find($id);

        // if request not found, redirect to request index
        if (!$request) {
            return $this->redirectToRoute('app_request');
        }

        return $this->render('request/show.html.twig', [
            'request' => $request,
        ]);
    }

    #[Route('/requests/{id}/accept', name: 'app_request_accept')]
    public function accept($id, RequestRepository $requestRepository, EntityManagerInterface $entityManager): Response
    {
        $request = $requestRepository->find($id);

        // if request not found, redirect to request index
        if (!$request) {
            return $this->redirectToRoute('app_request');
        }

        $request->setStatus(2);
        $entityManager->persist($request);
        $entityManager->flush();

        return $this->redirectToRoute('app_request');
    }

    #[Route('/requests/{id}/refuse', name: 'app_request_refuse')]
    public function refuse($id, RequestRepository $requestRepository, EntityManagerInterface $entityManager): Response
    {
        $request = $requestRepository->find($id);

        // if request not found, redirect to request index
        if (!$request) {
            return $this->redirectToRoute('app_request');
        }

        $request->setStatus(3);
        $entityManager->persist($request);
        $entityManager->flush();

        return $this->redirectToRoute('app_request');
    }
}
