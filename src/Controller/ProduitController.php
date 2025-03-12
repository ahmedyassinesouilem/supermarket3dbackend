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

            $entityManager->persist($produit);
            $entityManager->flush();

            $this->addFlash('success', 'Produit ajouté avec succès !');
            return $this->redirectToRoute('produit_new');
        }

        return $this->render('produit/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
