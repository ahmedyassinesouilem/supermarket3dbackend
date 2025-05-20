<?php
namespace App\Controller;

use App\Entity\User;
use App\Entity\Admin;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class RegistrationController extends AbstractController
{
    #[Route('/api/register', name: 'api_register', methods: ['POST'])]
public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
{
    try {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['email'], $data['plainPassword'], $data['nom'], $data['prenom'], $data['ville'], $data['adress'], $data['numTel'])) {
            throw new \Exception('Tous les champs sont requis : email, plainPassword, nom, prenom, ville, adress, numTel');
        }

        if ($entityManager->getRepository(User::class)->findOneBy(['email' => $data['email']])) {
            throw new \Exception('Cet email est déjà utilisé');
        }

        $user = new User();
        $user->setEmail($data['email']);
        $user->setNom($data['nom']);
        $user->setPrenom($data['prenom']);
        $user->setVille($data['ville']);
        $user->setAdress($data['adress']);
        $user->setNumTel((int)$data['numTel']);
        $user->setPassword($userPasswordHasher->hashPassword($user, $data['plainPassword']));
        $user->setRoles(['ROLE_USER']);
        $entityManager->persist($user);
        $entityManager->flush();

        return $this->json([
            'message' => 'Utilisateur inscrit avec succès',
        ], Response::HTTP_CREATED);

    } catch (\Throwable $e) {
        return $this->json([
            'error' => $e->getMessage(),
        ], 500);
    }
}
    #[Route ('/api/regigister_Admin', name: 'api_register_admin', methods: ['POST'])]
    public function registerAdmin(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        try {
            $data = json_decode($request->getContent(), true);

            if (!isset($data['email'], $data['plainPassword'], $data['nom'], $data['prenom'])) {
                throw new \Exception('Tous les champs sont requis : email, plainPassword, nom, prenom');
            }

            if ($entityManager->getRepository(Admin::class)->findOneBy(['email' => $data['email']])) {
                throw new \Exception('Cet email est déjà utilisé');
            }

            $admin = new Admin();
            $admin->setEmail($data['email']);
            $admin->setNom($data['nom']);
            $admin->setPrenom($data['prenom']);
            $admin->setPassword($userPasswordHasher->hashPassword($admin, $data['plainPassword']));
            $admin->setRoles(['ROLE_ADMIN']);
            $entityManager->persist($admin);
            $entityManager->flush();

            return $this->json([
                'message' => 'Admin inscrit avec succès',
            ], Response::HTTP_CREATED);

        } catch (\Throwable $e) {
            return $this->json([
                'error' => $e->getMessage(),
            ], 500);
        }
    }


}
