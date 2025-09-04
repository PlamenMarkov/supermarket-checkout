<?php

namespace App\Repository;

use App\Entity\Order;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class OrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }

    public function save(Order $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Order $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findWithItems(int $id): ?Order
    {
        return $this->createQueryBuilder('o')
            ->leftJoin('o.items', 'i')
            ->addSelect('i')
            ->andWhere('o.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findAllWithItems(): array
    {
        return $this->createQueryBuilder('o')
            ->leftJoin('o.items', 'i')
            ->addSelect('i')
            ->orderBy('o.id', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
