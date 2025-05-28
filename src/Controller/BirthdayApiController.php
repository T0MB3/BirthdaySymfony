<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class BirthdayApiController extends AbstractController
{
    #[Route('/birthday/api', name: 'app_birthday_api')]
    public function index(): Response
    {
        return $this->render('birthday_api/index.html.twig', [
            'controller_name' => 'BirthdayApiController',
        ]);
    }
}
