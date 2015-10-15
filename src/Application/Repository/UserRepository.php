<?php

namespace Application\Repository;

use Doctrine\ORM\EntityRepository;

class UserRepository
    extends EntityRepository
{
    public function countAll()
    {
        return $this->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function getEmployees()
    {
        $employees = array();

        $employeeObjects = $this->createQueryBuilder('u')
            ->leftJoin('u.roles', 'r')
            ->where('r.role = ?1')
            ->setParameter(1, 'ROLE_EMPLOYEE')
            ->getQuery()
            ->getResult()
        ;

        if ($employeeObjects) {
            foreach ($employeeObjects as $employeeObject) {
                $employees[] = $employeeObject->toArray();
            }
        }

        return $employees;
    }
}
