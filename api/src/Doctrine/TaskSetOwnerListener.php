<?php


namespace App\Doctrine;

use App\Entity\Task;
use Symfony\Component\Security\Core\Security;

class TaskSetOwnerListener
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function prePersist(Task $task)
    {
        if ($task->getOwner()) {
            return;
        }

        if ($this->security->getUser()) {
            $task->setOwner($this->security->getUser());
        }
    }
}

