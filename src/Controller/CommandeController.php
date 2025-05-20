<?php

namespace App\Controller;

use App\Entity\Commande;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/commande')]
class CommandeController extends AbstractController
{
    #[Route('/liste', name: 'commande_liste')]
    public function liste(EntityManagerInterface $em): Response
    {
        // Récupère toutes les commandes avec les produits associés
        $commandes = $em->getRepository(Commande::class)
                        ->findBy([], ['date' => 'DESC']); // Tri par date décroissante, par exemple

        return $this->render('commande/liste.html.twig', [
            'commandes' => $commandes,
        ]);
    }

    #[Route('/confirmer/{id}', name: 'commande_confirmer')]
    public function confirmer(int $id, EntityManagerInterface $em): Response
    {
        $commande = $em->getRepository(Commande::class)->find($id);

        if (!$commande) {
            $this->addFlash('error', "Commande non trouvée.");
            return $this->redirectToRoute('commande_liste');
        }

        $commande->setEtat(true);
        $em->flush();

        $this->addFlash('success', "Commande confirmée !");
        return $this->redirectToRoute('commande_liste');
    }
    #[Route('/annuler/{id}', name: 'commande_annuler')]
    public function annuler(int $id, EntityManagerInterface $em): Response
    {
        $commande = $em->getRepository(Commande::class)->find($id);

        if (!$commande) {
            $this->addFlash('error', "Commande non trouvée.");
            return $this->redirectToRoute('commande_liste');
        }

        $commande->setEtat(false);
        $em->flush();

        $this->addFlash('success', "Commande annulée !");
        return $this->redirectToRoute('commande_liste');
    }
    #[Route('/supprimer/{id}', name: 'commande_supprimer')]
    public function supprimer(int $id, EntityManagerInterface $em): Response
    {
        $commande = $em->getRepository(Commande::class)->find($id);

        if (!$commande) {
            $this->addFlash('error', "Commande non trouvée.");
            return $this->redirectToRoute('commande_liste');
        }

        $em->remove($commande);
        $em->flush();

        $this->addFlash('success', "Commande supprimée !");
        return $this->redirectToRoute('commande_liste');
    }
}
