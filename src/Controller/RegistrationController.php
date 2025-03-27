<?php
namespace App\Controller;

use App\Entity\User;
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
        $data = json_decode($request->getContent(), true);

        if (!isset($data['email'], $data['plainPassword'])) {
            throw new BadRequestHttpException('Email and plainPassword are required');
        }

        $user = new User();
        $user->setEmail($data['email']);
        
        // Assurer que l'email est unique (API Platform gère cela automatiquement)
        if ($entityManager->getRepository(User::class)->findOneBy(['email' => $data['email']])) {
            throw new BadRequestHttpException('This email is already registered');
        }

        // Encoder le mot de passe
        $plainPassword = $data['plainPassword'];
        $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

        $entityManager->persist($user);
        $entityManager->flush();

        return $this->json([
            'message' => 'User successfully registered',
            'user' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
            ]
        ], Response::HTTP_CREATED);
    }
}
