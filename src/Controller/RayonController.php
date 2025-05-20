<?php

namespace App\Controller;

use App\Entity\Rayon;
use App\Form\RayonType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RayonController extends AbstractController
{
    #[Route('/rayon/new', name: 'rayon_new')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $rayon = new Rayon();
        $form = $this->createForm(RayonType::class, $rayon);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($rayon);
            $entityManager->flush();
            $this->addFlash('success', 'Rayon ajouté avec succès !');
            return $this->redirectToRoute('rayon_new');
        }

        return $this->render('rayon/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    
}
