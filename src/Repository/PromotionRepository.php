<?php

namespace App\Repository;

use App\Entity\Product;
use App\Entity\Promotion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class PromotionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Promotion::class);
    }

    public function save(Promotion $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Promotion $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findByProduct(Product $product): array
    {
        return $this->createQueryBuilder('pr')
            ->andWhere('pr.product = :product')
            ->setParameter('product', $product)
            ->orderBy('pr.nQty', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findAll(): array
    {
        return $this->createQueryBuilder('pr')
            ->leftJoin('pr.product', 'p')
            ->addSelect('p')
            ->orderBy('pr.id', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
