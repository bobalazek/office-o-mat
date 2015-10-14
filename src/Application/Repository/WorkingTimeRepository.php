<?php

namespace Application\Repository;

use Doctrine\ORM\EntityRepository;

class WorkingTimeRepository
    extends EntityRepository
{
    public function countAll()
    {
        return $this->createQueryBuilder('wt')
            ->select('COUNT(wt.id)')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }
}
