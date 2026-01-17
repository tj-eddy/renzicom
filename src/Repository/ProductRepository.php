<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    /**
     * Find products by name
     *
     * @param string $name
     * @return Product[]
     */
    public function findByName(string $name): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.name LIKE :name')
            ->setParameter('name', '%'.$name.'%')
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find products by year
     *
     * @param int $year
     * @return Product[]
     */
    public function findByYear(int $year): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.yearEdition = :year')
            ->setParameter('year', $year)
            ->orderBy('p.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
