<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use JMS\Serializer\SerializerBuilder;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;

#[Route('/user')]
final class UserController extends AbstractController
{

    #[Route('/', name: 'app_user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        $serializer = SerializerBuilder::create()->build();
        $users = $userRepository->findAll();
        $json = $serializer->serialize($users, 'json');
        return new Response($json, 200, [
            'Content-Type' => 'application/json'
        ]);
    }
    #[Route('/{id}', name: 'app_user_show', methods: ['GET'])]
    public function showUser(UserRepository $userRepository, int $id): Response
    {
        $serializer = SerializerBuilder::create()->build();
        $user = $userRepository->find($id);
        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }
        $json = $serializer->serialize($user, 'json');
        return new Response($json, 200, [
            'Content-Type' => 'application/json'
        ]);
    }

    #[Route('/new', name: 'app_user_new', methods: ['POST'])]
    public function createUser(Request $request, UserRepository $userRepository, EntityManagerInterface $entityManager): Response
    {
        $serializer = SerializerBuilder::create()->build();
        $data = json_decode($request->getContent(), true);

        if (!$data || !isset($data['email'])) {
            return new JsonResponse(['error' => 'Invalid JSON data or name missing'], Response::HTTP_BAD_REQUEST);
        }

        $user = new User();
        $user->setEmail($data['email']);

        $entityManager->persist($user);
        $entityManager->flush();

        $json = $serializer->serialize($user, 'json');
        return new Response($json, Response::HTTP_CREATED, [
            'Content-Type' => 'application/json'
        ]);
    }

    #[Route('/{id}', name: 'app_user_delete', methods: ['POST'])]
    public function deleteUser(UserRepository $userRepository, EntityManagerInterface $entityManager, int $id): JsonResponse
    {
        $user = $userRepository->find($id);
        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        $entityManager->remove($user);
        $entityManager->flush();

        return new JsonResponse(['message' => 'User deleted successfully.'], Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'app_user_update', methods: ['PUT'])]
    public function updateUser(Request $request, UserRepository $userRepository, EntityManagerInterface $entityManager, int $id): Response
    {
        $serializer = SerializerBuilder::create()->build();
        $data = json_decode($request->getContent(), true);

        if (!$data || !isset($data['email'])) {
            return new JsonResponse(['error' => 'Invalid JSON data or name missing
'], Response::HTTP_BAD_REQUEST);
        }

        $user = $userRepository->find($id);
        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        $user->setEmail($data['email']);

        $entityManager->persist($user);
        $entityManager->flush();

        $json = $serializer->serialize($user, 'json');
        return new Response($json, Response::HTTP_OK, [
            'Content-Type' => 'application/json'
        ]);
    }
}
