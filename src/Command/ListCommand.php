<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Helper\Table;
use App\Controller\TaskController;
use App\Entity\Task;
use Doctrine\Persistence\ManagerRegistry;

#[AsCommand(
    name: 'app:list',
    description: 'List all existing tasks with detalied info',
)]
class ListCommand extends Command
{
    private $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;

        parent::__construct();
    }

    protected function configure(): void
    {
    
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $tasks = $this->doctrine->getRepository(Task::class)->findAll();

        if(count($tasks)==0){ 
            $io->success(sprintf('0 tasks found. You can start a new one typing app:task start "yourtaskname'));
            return Command::SUCCESS;
        }

        $table = new Table($output);
        $table->setHeaders(['Name', 'Status', 'Elapsed time', 'Started time','Ended time']);
        foreach($tasks as $task){
            $status = "Inactive";
            if($task->isStatusStarted()) $status = "Active";

            $endTime = $task->getTaskTimes()->last()->getEndTime();
            if($endTime==null){
                $endTime ="--";

            if (!$task->getTaskTimes()->isEmpty()){ 
                $taskTime = $task->getTaskTimes()->last();
                $taskTime->end();
                $task->addElapsedTime($taskTime); 
            }
            
            }else{
                $endTime = $endTime->format("H:i:s");
            }
            $table->addRow([
                $task->getName(),
                $status,$task->getElapsedTimeStr(),
                $task->getTaskTimes()->first()->getStartTime()->format("H:i:s"),
                $endTime
            ]);
        }
        
            
        $table->render();

        return Command::SUCCESS;
    }
}
