<?php

namespace App\Entity;

use App\Repository\TaskRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TaskRepository::class)]
class Task
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $name = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $status = 0;

    #[ORM\OneToMany(mappedBy: 'task', targetEntity: TaskTimes::class, orphanRemoval: true)]
    private Collection $taskTimes;

    #[ORM\Column(type: 'dateinterval')]
    private $elapsed_time;

    public function __construct()
    {
        $this->taskTimes = new ArrayCollection();
        $this->elapsed_time = new \DateInterval('PT0S');
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function isStatusStarted(): ?bool
    {
        if($this->status == 0) return false;
        return true;
    }

    public function isStatusStopped(): ?bool
    {
        if($this->status == 0) return true;
        return false;
    }

    public function setStatusStarted(): self
    {
        $this->status = 1;
        return $this;
    }

    public function setStatusStopped(): self
    {
        $this->status = 0;
        return $this;
    }

    /**
     * @return Collection<int, TaskTimes>
     */
    public function getTaskTimes(): Collection
    {
        return $this->taskTimes;
    }

    public function addTaskTime(TaskTimes $taskTime): self
    {
        if (!$this->taskTimes->contains($taskTime)) {
            $this->taskTimes->add($taskTime);
            $taskTime->setTask($this);
        }

        return $this;
    }

    public function removeTaskTime(TaskTimes $taskTime): self
    {
        if ($this->taskTimes->removeElement($taskTime)) {
            // set the owning side to null (unless already changed)
            if ($taskTime->getTask() === $this) {
                $taskTime->setTask(null);
            }
        }

        return $this;
    }

    public function getElapsedTime(): ?\DateInterval
    {
        return $this->elapsed_time;
    }

    public function setElapsedTime(\DateInterval $elapsed_time): self
    {
        $this->elapsed_time = $elapsed_time;

        return $this;
    }
}
