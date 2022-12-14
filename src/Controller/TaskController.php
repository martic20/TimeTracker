<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Task;
use App\Entity\TaskTimes;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Doctrine\ORM\Query\ResultSetMapping;


class TaskController extends AbstractController
{

    #[Route(path: '/', name: 'task_list', methods: ['GET','POST'])]
    public function home(Request $request, ManagerRegistry $doctrine): Response
    {
        $tasks = $doctrine->getRepository(Task::class)->findBy([],['id' => 'DESC']);
        $errorMsg = null;

        //to check if currently working in a task or not
        $startedTaskId = $this->checkForStartedTask($doctrine);
        $startedTask = $doctrine->getRepository(Task::class)->find($startedTaskId);
        
        $entityManager = $doctrine->getManager();
        
        //create and manage post request to the form for creating a new task
        $task = new Task();
        $form = $this->createFormBuilder($task)
            ->add('name', TextType::class)
            ->add('save', SubmitType::class, ['label' => 'Create Task'])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            //start new task
            if($this->startTask($doctrine, $task)){
                return $this->redirectToRoute('task_list');
            }else{
                $this->addFlash(
                    "danger",
                    "No s'ha pogut iniciar la tasca, prova de refrescar la pàgina."
                );
                return $this->redirectToRoute('task_list');
            }      
            
        }
        
        //for when refreshing the page having an started task active you 
        // not losing the temporal elapsed time
        //as we don't persist this modifications into the DB they don't have any effect for future requests
        if($startedTask!=null){ // if there's not any active task don't need to do this
            if (!$startedTask->getTaskTimes()->isEmpty()){ 
                $taskTime = $startedTask->getTaskTimes()->last();
                $taskTime->end();
                $startedTask->addElapsedTime($taskTime); 
            }
        }
        //do the same for the global task timer 
        $totalTime = $doctrine->getRepository(Task::class)->findElapsedTimeToday();
        $globalTask = new Task();
        $globalTask->setElapsedTime($totalTime);
        if($startedTask!=null) $globalTask->addElapsedTime($taskTime); 
        


        //render the page
        return $this->render('home.html.twig', [
            'tasks' => $tasks,
            'form' => $form->createView(),
            'startedTaskId' => $startedTaskId,
            'globalTask' => $globalTask,
            'startedTask' => $startedTask
        ]);
        
    }

    #[Route(path: '/task_stop/', name: 'task_stop', methods: ['GET'])]
    public function stopTaskAjax(Request $request, ManagerRegistry $doctrine): Response
    {
        $taskId = $request->query->get('taskId', -1);
        if ($taskId == -1)return new Response("error");
        
        $task = $doctrine->getRepository(Task::class)->find($taskId);

        if ($task == null) return new Response("error");
        
        if($this->stopTask($doctrine, $task)){
            return new Response("success");
        }else{
            return new Response("error");
        }        
    }

    #[Route(path: '/task_resume/', name: 'task_resume', methods: ['GET'])]
    public function resumeTaskAjax(Request $request, ManagerRegistry $doctrine): Response
    {
        $taskId = $request->query->get('taskId', -1);
        if ($taskId == -1)return new Response("error");
        
        $task = $doctrine->getRepository(Task::class)->find($taskId);

        if ($this->startTask($doctrine,$task)) return new Response("success");

        return new Response("error");
    }

    /*
        Used as a secure step before starting a new task or taskTimes
        to ensure DB data integrity, so not havint two open tasks at the same time, 
        or two open taskTimes without closing them

        @return int: if -1 no task found, else task_id
    */
    private function checkForStartedTask(ManagerRegistry $doctrine): int
    {        
        $startedTask = $doctrine->getRepository(Task::class)->findStartedTask();
        if(count($startedTask)==1){
            return $startedTask[0]->getId();
        }else{
            return -1;
        }
    }
    
    public function startTask(ManagerRegistry $doctrine, Task $task ): bool
    {
        $entityManager = $doctrine->getManager();
        
        if ($this->checkForStartedTask($doctrine)!=-1){
            return false;
        }
        
        //if has the same name, add the time to the already existing task
        $existingTask = $doctrine->getRepository(Task::class)->findBy(["name" => $task->getName()]);
        if(count($existingTask)==1){
            $task = $existingTask[0];
        }

        $task->setStatusStarted();
        $task->createTaskTime();
        $entityManager->persist($task);
        $entityManager->flush();

        return true;
    }

    public function stopTask(ManagerRegistry $doctrine, $task):bool{      
        if ($task == null) return false;
        if($task->getTaskTimes()->isEmpty()) return false;
        $taskTime = $task->getTaskTimes()->last();
        
        if($taskTime->end() == false) return false;
        $task->addElapsedTime($taskTime);
        $task->setStatusStopped();

        $entityManager = $doctrine->getManager();
        $entityManager->persist($task);
        $entityManager->flush();

        return true;
    }
}
