<?php

namespace App\Controller;

use App\Entity\Birthday;
use App\Form\BirthdayForm;
use App\Repository\BirthdayRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\SerializerBuilder;

#[Route('/birthday')]
final class BirthdayController extends AbstractController
{

    #[Route('/', name: 'app_birthday_index', methods: ['GET'])]

    public function index(BirthdayRepository $birthdayRepository): Response
    {
        $serializer = SerializerBuilder::create()->build();
        $birthdays = $birthdayRepository->findAll();
        $json = $serializer->serialize($birthdays, 'json');
        return new Response($json, 200, [
            'Content-Type' => 'application/json'
        ]);
    }

    #[Route('/new', name: 'app_birthday_new', methods: ['GET', 'POST'])]
    public function new(Request $request, BirthdayRepository $birthdayRepository): Response
    {
        $serializer = SerializerBuilder::create()->build();
        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return new JsonResponse(['error' => 'Invalid JSON data'], Response::HTTP_BAD_REQUEST);
        }

        $date = isset($data['birthday']) ? \DateTimeImmutable::createFromFormat('Y-m-d', $data['birthday']) : null;
        if (!$date) {
            return new JsonResponse(['error' => 'Invalid date format'], Response::HTTP_BAD_REQUEST);
        }

        $birthday = new Birthday();
        $birthday->setBirthday($date);
        $birthday->setUsers($data['users'] ?? '');

        $birthdayRepository->add($birthday, true);

        $json = $serializer->serialize($birthday, 'json');
        return new Response($json, Response::HTTP_CREATED, [
            'Content-Type' => 'application/json'
        ]);
    }


    #[Route('/{id}', name: 'app_birthday_show', methods: ['GET'])]
    public function show(Birthday $birthday): Response
    {

        $serializer = SerializerBuilder::create()->build();

        $json = $serializer->serialize($birthday, 'json');
        return new Response($json, Response::HTTP_CREATED, [
            'Content-Type' => 'application/json'
        ]);
    }

    #[Route('/{id}', name: 'app_birthday_edit', methods: ['PUT'])]
    public function edit(Request $request, Birthday $birthday, EntityManagerInterface $entityManager): Response
    {
        $serializer = SerializerBuilder::create()->build();
        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return new JsonResponse(['error' => 'Invalid JSON data'], Response::HTTP_BAD_REQUEST);
        }

        if (isset($data['birthday'])) {
            $date = \DateTimeImmutable::createFromFormat('Y-m-d', $data['birthday']);
            if (!$date) {
                return new JsonResponse(['error' => 'Invalid date format'], Response::HTTP_BAD_REQUEST);
            }
            $birthday->setBirthday($date);
        }

        if (isset($data['users'])) {
            $birthday->setUsers($data['users']);
        }

        $entityManager->flush();

        $json = $serializer->serialize($birthday, 'json');
        return new Response($json, Response::HTTP_OK, [
            'Content-Type' => 'application/json'
        ]);
    }

    #[Route('/{id}', name: 'app_birthday_delete', methods: ['POST'])]
    public function delete(Birthday $birthday, EntityManagerInterface $entityManager): JsonResponse
    {
        $entityManager->remove($birthday);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Anniversaire supprimé avec succès.'], Response::HTTP_OK);
    }
    #[Route('/users', name: 'app_birthday_users', methods: ['GET'])]
    public function getUsers(Birthday $birthday): Response
    {
        $serializer = SerializerBuilder::create()->build();
        $users = $birthday->getUsers();

        if (!$users) {
            return new JsonResponse(['error' => 'No users found for this birthday'], Response::HTTP_NOT_FOUND);
        }

        $json = $serializer->serialize($users, 'json');
        return new Response($json, 200, [
            'Content-Type' => 'application/json'
        ]);
    }

    #[Route('/users/{id}', name: 'app_birthday_add_user', methods: ['POST'])]
    public function addUserToBirthday(Request $request, UserRepository $userRepository, EntityManagerInterface $entityManager, int $id): Response
    {
        $serializer = SerializerBuilder::create()->build();
        $data = json_decode($request->getContent(), true);

        if (!$data || !isset($data['userId'])) {
            return new JsonResponse(['error' => 'Invalid JSON data or userId missing'], Response::HTTP_BAD_REQUEST);
        }

        $birthday = $userRepository->find($id);
        if (!$birthday) {
            return new JsonResponse(['error' => 'Birthday not found'], Response::HTTP_NOT_FOUND);
        }

        $userId = $data['userId'];
        // Assuming you have a User entity and repository to fetch the user by ID
        // $user = $this->getDoctrine()->getRepository(User::class)->find($userId);
        // if (!$user) {
        //     return new JsonResponse(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        // }

        // Add the user to the birthday (assuming setUsers accepts an array of user IDs)
        // $birthday->addUser($user); // Adjust this line based on your actual method to add users

        $entityManager->flush();

        $json = $serializer->serialize($birthday, 'json');
        return new Response($json, Response::HTTP_OK, [
            'Content-Type' => 'application/json'
        ]);
    }

    #[Route('/{id}/users/{userId}', name: 'app_birthday_remove_user', methods: ['DELETE'])]
    public function removeUserFromBirthday(Birthday $birthday, int $userId, EntityManagerInterface $entityManager): Response
    {
        // Assuming you have a method to remove a user from the birthday
        // $birthday->removeUser($userId); // Adjust this line based on your actual method to remove users

        $entityManager->flush();

        return new JsonResponse(['message' => 'User removed from birthday successfully.'], Response::HTTP_OK);
    }

    #[Route('/{id}/users', name: 'app_birthday_update_users', methods: ['PUT'])]
    public function updateUsers(Request $request, Birthday $birthday, EntityManagerInterface $entityManager): Response
    {
        $serializer = SerializerBuilder::create()->build();
        $data = json_decode($request->getContent(), true);

        if (!$data || !isset($data['users'])) {
            return new JsonResponse(['error' => 'Invalid JSON data or users missing'], Response::HTTP_BAD_REQUEST);
        }

        // Assuming setUsers accepts an array of user IDs
        $birthday->setUsers($data['users']);

        $entityManager->flush();

        $json = $serializer->serialize($birthday, 'json');
        return new Response($json, Response::HTTP_OK, [
            'Content-Type' => 'application/json'
        ]);
    }
}
