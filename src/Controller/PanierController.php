<?php

namespace App\Controller;

use App\Entity\Panier;
use App\Entity\PanierProduit;
use App\Entity\Produits;
use App\Entity\Commande;
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
        $user = $this->getUser(); 

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
    #[Route('/afficher' , name: 'afficher', methods: ['GET'])]
    public function afficherPanier(PanierRepository $panierRepo): JsonResponse
    {
        $user = $this->getUser(); 
        if(!$user){
            return new JsonResponse(['message'=>'Utilisateur non connecté'], 401);
        }
        $panier = $panierRepo->findOneBy(['user' => $user]);
        if (!$panier){
            return new JsonResponse(['message' => 'Panier vide'] ,404);
        }
        $produits = [];
        foreach ($panier->getPanierProduits() as $panierProduit){
            $produits[]= [
                'id' => $panierProduit->getProduit()->getId(),
                'produit' => $panierProduit->getProduit()->getNom(),
                'quantite' => $panierProduit->getQuantite(),
                'prix' => $panierProduit->getProduit()->getPrix(),
            ]
            ;
        }
        return new JsonResponse([
            'panier' => [
                'id' => $panier->getId(),
                'produits' => $produits,
                'total' => array_reduce($produits, function ($carry, $item) {
                    return $carry + ($item['prix'] * $item['quantite']);
                }, 0)
            ]
        ]);
        

    }
    #[Route('/checkout', name: 'checkout', methods: ['POST'])]
    public function checkout(
        EntityManagerInterface $entityManager,
        PanierRepository $panierRepo,
        PanierProduitRepository $panierProduitRepo
    ): JsonResponse {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['message' => 'Utilisateur non connecté'], 401);
        }
    
        $panier = $panierRepo->findOneBy(['user' => $user]);
        if (!$panier || count($panier->getPanierProduits()) === 0) {
            return new JsonResponse(['message' => 'Panier vide'], 400);
        }
    
        // Création de la commande
        $commande = new \App\Entity\Commande();
        $commande->setUser($user);
        $commande->setDate(new \DateTime());
        $commande->setEtat(false);
    
        $entityManager->persist($commande);
    
        // Associer les PanierProduits existants à la commande
        foreach ($panier->getPanierProduits() as $panierProduit) {
            $panierProduit->setCommande($commande);
            $panierProduit->setPanier(null); 
            $commande->addCommandeProduit($panierProduit);
        }
    
        $entityManager->flush();
    
        return new JsonResponse([
            'message' => 'Commande créée avec succès',
            'commande_id' => $commande->getId(),
            'etat' => $commande->getEtat(),
            'date' => $commande->getDate()->format(\DateTime::ATOM),
        ]);
    }
    
#[Route('/supprimer', name: 'supprimer_produit', methods: ['DELETE'])]
public function supprimerProduit(
    Request $request,
    EntityManagerInterface $entityManager,
    PanierRepository $panierRepo,
    ProduitsRepository $produitsRepo,
    PanierProduitRepository $panierProduitRepo
): JsonResponse {
    $user = $this->getUser();
    if (!$user) {
        return new JsonResponse(['message' => 'Utilisateur non connecté'], 401);
    }

    $data = json_decode($request->getContent(), true);
    $produitId = $data['produitId'] ?? null;

    if (!$produitId) {
        return new JsonResponse(['message' => 'ID produit manquant'], 400);
    }

    $panier = $panierRepo->findOneBy(['user' => $user]);
    if (!$panier) {
        return new JsonResponse(['message' => 'Panier introuvable'], 404);
    }

    $produit = $produitsRepo->find($produitId);
    if (!$produit) {
        return new JsonResponse(['message' => 'Produit non trouvé'], 404);
    }

    $panierProduit = $panierProduitRepo->findOneBy([
        'panier' => $panier,
        'produit' => $produit
    ]);

    if (!$panierProduit) {
        return new JsonResponse(['message' => 'Produit non présent dans le panier'], 404);
    }

    $entityManager->remove($panierProduit);
    $entityManager->flush();

    return new JsonResponse(['message' => 'Produit supprimé du panier']);
}
#[Route('/commandes', name: 'api_commandes_lister', methods: ['GET'])]
public function listerCommandes(): JsonResponse
{
    $user = $this->getUser();
    if (!$user) {
        return new JsonResponse(['message' => 'Utilisateur non connecté'], 401);
    }

    $commandes = $user->getCommandes(); // Assure-toi que la relation est bien définie dans l'entité User

    $result = [];
    foreach ($commandes as $commande) {
        $produits = [];
        foreach ($commande->getCommandeProduits() as $commandeProduit) {
            $produits[] = [
                'id' => $commandeProduit->getProduit()->getId(),
                'nom' => $commandeProduit->getProduit()->getNom(),
                'quantite' => $commandeProduit->getQuantite(),
                'prix' => $commandeProduit->getProduit()->getPrix()
            ];
        }

        $result[] = [
            'id' => $commande->getId(),
            'date' => $commande->getDate()->format('Y-m-d H:i:s'),
            'etat' => $commande->getEtat(),
            'produits' => $produits
        ];
    }

    return new JsonResponse(['commandes' => $result]);
}




}
