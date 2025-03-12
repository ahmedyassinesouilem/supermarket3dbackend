<?php
// src/Controller/ProduitApiController.php
namespace App\Controller;

use App\Entity\Produits;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use App\Repository\ProduitRepository;
#[Route('/api/produits')]
class ProduitApiController extends AbstractController
{
    #[Route('', name: 'api_produits_list', methods: ['GET'])]
    public function list(EntityManagerInterface $entityManager): JsonResponse
    {
        $produits = $entityManager->getRepository(Produits::class)->findAll();
        $data = [];

        foreach ($produits as $produit) {
            $data[] = [
                'id' => $produit->getId(),
                'nom' => $produit->getNom(),
                'prix' => $produit->getPrix(),
                'modelPath' => $produit->getModelPath(),
                'rayon' => $produit->getRayon() ? $produit->getRayon()->getId() : null,
            ];
        }

        return $this->json($data, 200, [
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE',
            'Access-Control-Allow-Headers' => 'Content-Type'
        ]);
    }

    #[Route('/api/produits', name: 'api_produits', methods: ['GET'])]
    public function getProduits(ProduitRepository $produitRepository): JsonResponse
    {
        $produits = $produitRepository->findAll();
        $data = [];
    
        foreach ($produits as $produit) {
            $data[] = [
                'id' => $produit->getId(),
                'nom' => $produit->getNom(),
                'prix' => $produit->getPrix(),
                'rayon' => $produit->getRayon()->getId(),
               'modelPath' => $produit->getModelPath(),
            ];
        }
    
        return $this->json($data);
    }
    

    #[Route('', name: 'api_produit_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $produit = new Produits();
        $produit->setNom($data['nom'] ?? '');
        $produit->setPrix($data['prix'] ?? 0);

        // Upload du fichier si présent
        $modelFile = $request->files->get('model');
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
                return $this->json(['error' => "Erreur d'upload : " . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        $entityManager->persist($produit);
        $entityManager->flush();

        return $this->json(['message' => 'Produit ajouté !'], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'api_produit_update', methods: ['PUT'])]
    public function update(Request $request, Produits $produit, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (isset($data['nom'])) {
            $produit->setNom($data['nom']);
        }
        if (isset($data['prix'])) {
            $produit->setPrix($data['prix']);
        }

        $entityManager->flush();

        return $this->json(['message' => 'Produit mis à jour !']);
    }

    #[Route('/{id}', name: 'api_produit_delete', methods: ['DELETE'])]
    public function delete(Produits $produit, EntityManagerInterface $entityManager): JsonResponse
    {
        $entityManager->remove($produit);
        $entityManager->flush();

        return $this->json(['message' => 'Produit supprimé !']);
    }


    #[Route('/models/{filename}', name: 'model_serve', methods: ['GET'])]
public function serveModel(string $filename): Response
{
    $projectDir = $this->getParameter('kernel.project_dir');
    $filePath = $projectDir . '/public/uploads/models/' . $filename;

    if (!file_exists($filePath)) {
        return new JsonResponse(['error' => 'Fichier non trouvé'], 404);
    }

    return new BinaryFileResponse($filePath, 200, [
        'Access-Control-Allow-Origin' => '*'
    ]);
}

}
