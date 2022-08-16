<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use App\Controller\TaskController;
use App\Entity\Task;
use Doctrine\Persistence\ManagerRegistry;

#[AsCommand(
    name: 'app:task',
    description: 'To start or stop a task in the TrackTimer app',
)]
class TaskCommand extends Command
{
    private $taskController;
    private $doctrine;

    public function __construct(TaskController $taskController,ManagerRegistry $doctrine)
    {
        $this->taskController = $taskController;
        $this->doctrine = $doctrine;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('action', InputArgument::REQUIRED, 'start or end the task')
            ->addArgument('name', InputArgument::REQUIRED, 'Name of the task')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $action = $input->getArgument('action');
        $name = $input->getArgument('name');

        if ($action=="start") {
            $task = new Task();
            $task->setName($name);
            if($this->taskController->startTask($this->doctrine, $task)){
                $io->success(sprintf('Task %s started!', $name));
                return Command::SUCCESS;
            }else{
                $io->error(sprintf("Right now there's already an active task, stop first it before starting a new one."));
                return Command::INVALID;
            }

        }elseif($action=="end"){
            $task = $this->doctrine->getRepository(Task::class)->findBy(["name" => $name]);
            if(count($task)!=1){
                $io->error(sprintf("The specified task doesn't exist."));
                return Command::INVALID;
            }
            if($this->taskController->stopTask($this->doctrine, $task[0])){
                $io->success(sprintf('Task %s ended!', $name));
                return Command::SUCCESS;
            }else{
                $io->error(sprintf("This task is not active, so you cannot end it."));
                return Command::INVALID;
            }

        }else{
            $io->error('The action has to be "start" or "end"');
            return Command::INVALID;
        }

        
        
    }
}
