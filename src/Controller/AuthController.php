<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class AuthController extends AbstractController
{
    private $passwordHasher;
    private $entityManager;

    public function __construct(UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager)
    {
        $this->passwordHasher = $passwordHasher;
        $this->entityManager = $entityManager;
    }

    #[Route('/auth', name: 'app_auth')]
    public function index(): Response
    {
        return $this->render('auth/index.html.twig', [
            'controller_name' => 'AuthController',
        ]);
    }

    #[Route('/register', name:'register')]
    public function register(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordEncoder): JsonResponse{

        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        $form->submit($request->toArray());
    
        if ($form->isSubmitted() && $form->isValid()) {
            // Хешируем пароль перед сохранением
            $password = $passwordEncoder->hashPassword($user, $user->getPassword());
            $user->setPassword($password);
    
            // Сохраняем пользователя в базе данных
            $entityManager->persist($user); 
            $entityManager->flush();            
    
            return $this->json('Welcome!', Response::HTTP_CREATED);
        }
    
        return $this->json(['Form is not valid', $form]);
    }

    #[Route('/login', name: 'login')]
    public function auth(#[CurrentUser] ?User $user, Request $request): JsonResponse{
        if (null === $user) {
            return $this->json([
                'message' => 'missing credentials',
            ], Response::HTTP_UNAUTHORIZED);
        }
            
         // somehow create an API token for $user
        $token = 'sigma';   

        return $this->json([
            'user'  => $user->getUserIdentifier(),                 
            'token' => $token
        ]);
    }

    //проверка на аутентификацию
    #[Route('/check-auth', name: 'check_auth')]
    public function checkAuth(#[CurrentUser] ?User $user): JsonResponse
    {
        return $this->json([
            'authenticated' => $user !== null,
            'user' => $user ? $user->getUserIdentifier() : null,
        ]);
    }


    #[Route('/logout', name:'logout')]
    public function logout(): JsonResponse{
        return $this->json([
            'message' => 'Successfully logout!'
        ]);
    }
}
