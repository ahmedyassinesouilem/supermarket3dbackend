<?php
// src/Controller/ProduitController.php
namespace App\Controller;

use App\Entity\Produits;
use App\Form\ProduitType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
#[Route('/produit')]
class ProduitController extends AbstractController
{
    #[Route('/new', name: 'produit_new')]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $produit = new Produits();
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        dump($_FILES); // Debugging pour voir si le fichier est bien envoyé

        if ($form->isSubmitted() && $form->isValid()) {
            $modelFile = $form->get('model')->getData();
            if ($modelFile) {
                try {
                    $originalFilename = pathinfo($modelFile->getClientOriginalName(), PATHINFO_FILENAME);
                    $safeFilename = $slugger->slug($originalFilename);
                    $newFilename = $safeFilename . '-' . uniqid() . '.' . $modelFile->guessExtension();

                    $uploadDir = $this->getParameter('models_directory');
                    if (!$uploadDir) {
                        throw new \RuntimeException("The 'models_directory' parameter is not defined.");
                    }

                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }

                    $modelFile->move($uploadDir, $newFilename);
                    $produit->setModelPath($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', "Erreur d'upload : " . $e->getMessage());
                    return $this->redirectToRoute('produit_new');
                }
            }
            $photoFile = $form->get('photo')->getData();
if ($photoFile) {
    try {
        $originalFilename = pathinfo($photoFile->getClientOriginalName(), PATHINFO_FILENAME);
        $uploadDir = $this->getParameter('photos_directory');
        if (!$uploadDir) {
            throw new \RuntimeException("The 'photos_directory' parameter is not defined.");
        }
        $newFilename = $safeFilename . '-' . uniqid() . '.' . $photoFile->guessExtension();

        $uploadDir = $this->getParameter('photos_directory');

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $photoFile->move($uploadDir, $newFilename);
        $produit->setPhoto($newFilename);
    } catch (FileException $e) {
        $this->addFlash('error', "Erreur d'upload de la photo : " . $e->getMessage());
        return $this->redirectToRoute('produit_new');
    }
} else {
    $this->addFlash('error', "La photo est obligatoire.");
    return $this->redirectToRoute('produit_new');
}
            

            $entityManager->persist($produit);
            $entityManager->flush();

            $this->addFlash('success', 'Produit ajouté avec succès !');
            return $this->redirectToRoute('produit_new');
        }

        return $this->render('produit/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    #[Route('/afficherproduits', name: 'afficher_produits', methods: ['GET'])]
    public function afficherProduits(EntityManagerInterface $entityManager): Response
    {
        $produits = $entityManager->getRepository(Produits::class)->findAll();

        return $this->render('produit/afficherproduits.html.twig', [
            'produits' => $produits,
        ]);
    }
    #[Route('/modifier/{id}', name: 'produit_modifier')]
    public function modifier(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger, int $id): Response
    {
        $produit = $entityManager->getRepository(Produits::class)->find($id);

        if (!$produit) {
            throw $this->createNotFoundException('Produit non trouvé');
        }

        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gérer le fichier model
            $modelFile = $form->get('model')->getData();
            if ($modelFile) {
                try {
                    $originalFilename = pathinfo($modelFile->getClientOriginalName(), PATHINFO_FILENAME);
                    $safeFilename = $slugger->slug($originalFilename);
                    $newFilename = $safeFilename . '-' . uniqid() . '.' . $modelFile->guessExtension();

                    // Déplacer le fichier
                    $uploadDir = $this->getParameter('models_directory');
                    if (!$uploadDir) {
                        throw new \RuntimeException("The 'models_directory' parameter is not defined.");
                    }

                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }

                    $modelFile->move($uploadDir, $newFilename);
                    $produit->setModelPath($newFilename);
                } catch (FileException $e) {
                    // Gérer l'erreur d'upload
                    return new Response("Erreur d'upload : " . $e->getMessage(), 500);
                }
            }

            $photoFile = $form->get('photo')->getData();
            if ($photoFile) {
                try {
                    $originalFilename = pathinfo($photoFile->getClientOriginalName(), PATHINFO_FILENAME);
                    $safeFilename = $slugger->slug($originalFilename);
                    $newFilename = $safeFilename . '-' . uniqid() . '.' . $photoFile->guessExtension();
            
                    $uploadDir = $this->getParameter('photos_directory');
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }
            
                    $photoFile->move($uploadDir, $newFilename);
                    $produit->setPhoto($newFilename);
                } catch (FileException $e) {
                    return new Response("Erreur d'upload de la photo : " . $e->getMessage(), 500);
                }
            }
            

            // Enregistrer les modifications dans la base de données
            $entityManager->flush();

            $this->addFlash('success', 'Produit modifié avec succès !');
            return $this->redirectToRoute('afficher_produits');
            
        }

        return $this->render('produit/modifier.html.twig', [
            'form' => $form->createView(),
        ]);
        
    }
    #[Route('/supprimer/{id}', name: 'produit_supprimer', methods: ['GET', 'POST'])]
    public function supprimer(int $id, EntityManagerInterface $entityManager): Response
    {
        $produit = $entityManager->getRepository(Produits::class)->find($id);
    
        if (!$produit) {
            $this->addFlash('error', 'Produit non trouvé.');
            return $this->redirectToRoute('afficher_produits');
        }
    
        // Supprimer les PanierProduits liés
        $panierProduits = $entityManager->getRepository(\App\Entity\PanierProduit::class)
            ->findBy(['produit' => $produit]);
    
        foreach ($panierProduits as $panierProduit) {
            $entityManager->remove($panierProduit);
        }
    
        // Supprimer le produit lui-même
        $entityManager->remove($produit);
        $entityManager->flush();
    
        $this->addFlash('success', 'Produit supprimé avec succès.');
        return $this->redirectToRoute('afficher_produits');
    }
    
    
}
