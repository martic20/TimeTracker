<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Task;

class TaskController extends AbstractController
{
    #[Route('/start_task', name: 'create_task')]
    public function startTask(ManagerRegistry $doctrine ): Response
    {
        $entityManager = $doctrine->getManager();

        $task = new Task();
        $task->setName("df22");

        $errors = $validator->validate($task);
        if (count($errors) > 0) {
            return new Response((string) $errors, 400);
        }

        $entityManager->persist($task);
        $entityManager->flush();

        return new Response('New task with id'.$task->getId().' created!');
    }
}
