<?php

namespace App\Controller;

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
        $isAdmin = in_array('ROLE_ADMIN', $this->getUser()->getRoles());
        $latestApprovedRequest = $requestRepository->getLastAcceptedRequests();

        $offset = max(0, $request->query->getInt('offset', 0));

        if ($isAdmin) {
            // if admin, only get pending requests
            $paginator = $requestRepository->getRequestPaginator($offset, null, 1);
        } else {
            // else get requests of user
            $paginator = $requestRepository->getRequestPaginator($offset, $this->getUser());
        }

        $requests = $requestRepository->findAll();

        return $this->render('request/index.html.twig', [
            'requests'              => $paginator,
            'latestApprovedRequest' => $latestApprovedRequest,
            'isAdmin'               => $isAdmin,
            'previous'              => $offset - RequestRepository::PAGINATOR_PER_PAGE,
            'next'                  => min(count($requests), $offset + RequestRepository::PAGINATOR_PER_PAGE),
        ]);
    }

    #[Route('/request/make', name: 'app_request_make')]
    public function makeRequest(RequestRepository $requestRepository): Response
    {
        $user = $this->getUser();

        return $this->render('request/make.html.twig', [

        ]);
    }

    #[Route('/requests/{id}', name: 'app_request_show')]
    public function show($id, RequestRepository $requestRepository): Response
    {
        $request = $requestRepository->find($id);
        $isAdmin = in_array('ROLE_ADMIN', $this->getUser()->getRoles());

        return $this->render('request/show.html.twig', [
            'request' => $request,
            'isAdmin' => $isAdmin,
        ]);
    }

    #[Route('/requests/{id}/accept', name: 'app_request_accept')]
    public function accept($id, RequestRepository $requestRepository, EntityManagerInterface $entityManager): Response
    {
        $request = $requestRepository->find($id);
        $request->setStatus(2);
        $entityManager->persist($request);
        $entityManager->flush();

        return $this->redirectToRoute('app_request');
    }

    #[Route('/requests/{id}/refuse', name: 'app_request_refuse')]
    public function refuse($id, RequestRepository $requestRepository, EntityManagerInterface $entityManager): Response
    {
        $request = $requestRepository->find($id);
        $request->setStatus(3);
        $entityManager->persist($request);
        $entityManager->flush();

        return $this->redirectToRoute('app_request');
    }
}
