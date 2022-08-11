<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Task;

class TaskController extends AbstractController
{
    #[Route('/start_task', name: 'create_task')]
    public function startTask(ManagerRegistry $doctrine): JsonResponse
    {
        $entityManager = $doctrine->getManager();

        $task = new Task();
        $task->setName("df22");

        $entityManager->persist($task);
        $entityManager->flush();

        return $this->json([
            'message' => 'New task with id'.$task->getId().' created!',
            'path' => 'src/Controller/TaskController.php',
        ]);
    }
}
