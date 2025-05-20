<?php

namespace App\Controller;

use App\Entity\Etagers;
use App\Form\EtagersType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;

#[Route('/etagers', name: 'app_etagers')]
final class EtagersController extends AbstractController
{
    #[Route('/new', name: '_new')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $etager = new Etagers();
        $form = $this->createForm(EtagersType::class, $etager);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($etager);
            $entityManager->flush();

            $this->addFlash('success', 'Étagère ajoutée avec succès !');
            return $this->redirectToRoute('app_etagers_new');
        }

        return $this->render('etagers/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    #[Route('/by-rayon/{rayonId}', name: 'etagers_by_rayon', methods: ['GET'])]
public function getEtagersByRayon(int $rayonId, EntityManagerInterface $em): Response
{
    $etagers = $em->getRepository(\App\Entity\Etagers::class)->findBy(['rayon' => $rayonId]);

    $result = [];
    foreach ($etagers as $etager) {
        $result[] = [
            'id' => $etager->getId(),
            'num' => $etager->getNum(),
        ];
    }

    return $this->json($result);
}

  

}
