<?php

namespace App\Controller;

use App\Entity\Panier;
use App\Entity\PanierProduit;
use App\Entity\Produits;
use App\Repository\PanierRepository;
use App\Repository\PanierProduitRepository;
use App\Repository\ProduitsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/panier', name: 'api_panier_')]
class PanierController extends AbstractController
{
    #[Route('/ajouter', name: 'ajouter_produit', methods: ['POST'])]
    public function ajouterProduit(
        Request $request, 
        EntityManagerInterface $entityManager, 
        ProduitsRepository $produitsRepo, 
        PanierRepository $panierRepo, 
        PanierProduitRepository $panierProduitRepo
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $produitId = $data['produitId'] ?? null;
        $quantite = $data['quantite'] ?? 1;
        $user = $this->getUser(); // Récupérer l'utilisateur connecté

        if (!$produitId || !$user) {
            return new JsonResponse(['message' => 'Données invalides'], 400);
        }

        $produit = $produitsRepo->find($produitId);
        if (!$produit) {
            return new JsonResponse(['message' => 'Produit non trouvé'], 404);
        }

        // Vérifier si l'utilisateur a déjà un panier
        $panier = $panierRepo->findOneBy(['user' => $user]);
        if (!$panier) {
            $panier = new Panier();
            $panier->setUser($user);
            $entityManager->persist($panier);
            $entityManager->flush();
        }

        // Vérifier si le produit est déjà dans le panier
        $panierProduit = $panierProduitRepo->findOneBy(['panier' => $panier, 'produit' => $produit]);
        
        if ($panierProduit) {
            // Augmenter la quantité
            $panierProduit->setQuantite($panierProduit->getQuantite() + $quantite);
        } else {
            // Ajouter un nouveau produit au panier
            $panierProduit = new PanierProduit();
            $panierProduit->setPanier($panier);
            $panierProduit->setProduit($produit);
            $panierProduit->setQuantite($quantite);
            $entityManager->persist($panierProduit);
        }

        $entityManager->flush();

        return new JsonResponse([
            'message' => 'Produit ajouté au panier',
            'produit' => $produit->getNom(),
            'quantite' => $panierProduit->getQuantite()
        ], 201);
    }
}
