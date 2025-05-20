<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Repository\RayonRepository;
#[Route('/api/rayons')]
final class RayonapiController extends AbstractController
{
    #[Route('', name: 'api_rayons_list', methods: ['GET'])]
    public function list(RayonRepository $rayonRepository): JsonResponse
    {
        $rayons = $rayonRepository->findAll();

        $data = [];
        foreach ($rayons as $rayon) {
            $data[] = [
                'id' => $rayon->getId(),
                'nom' => $rayon->getNom()
            ];
        }

        return $this->json($data);
    }
}
