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
    #[Route('/requests', name: 'app_request')]
    public function index(Request $request, RequestRepository $requestRepository): Response
    {
        $offset = max(0, $request->query->getInt('offset', 0));
        $paginator = $requestRepository->getRequestPaginator($offset);

        $requests = $requestRepository->findAll();

        return $this->render('request/index.html.twig', [
            'requests' => $paginator,
            'previous' => $offset - RequestRepository::PAGINATOR_PER_PAGE,
            'next' => min(count($requests), $offset + RequestRepository::PAGINATOR_PER_PAGE),
        ]);
    }

    #[Route('/requests/{id}', name: 'app_request_show')]
    public function show($id, RequestRepository $requestRepository): Response
    {
        $request = $requestRepository->find($id);
        $roles = $this->getUser()->getRoles();
        $isAdmin = in_array('ROLE_ADMIN', $roles);

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
