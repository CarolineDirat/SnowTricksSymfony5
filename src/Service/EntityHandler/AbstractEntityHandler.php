<?php

namespace App\Service\EntityHandler;

use Doctrine\Common\Persistence\ManagerRegistry;

class AbstractEntityHandler
{
    protected ManagerRegistry $managerRegistry;

    public function __construct(ManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
    }
}
