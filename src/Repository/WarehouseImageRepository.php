<?php

namespace App\Repository;

use App\Entity\WarehouseImage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<WarehouseImage>
 *
 * @method WarehouseImage|null find($id, $lockMode = null, $lockVersion = null)
 * @method WarehouseImage|null findOneBy(array $criteria, array $orderBy = null)
 * @method WarehouseImage[]    findAll()
 * @method WarehouseImage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WarehouseImageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WarehouseImage::class);
    }

    /**
     * Sauvegarder une image
     */
    public function save(WarehouseImage $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Supprimer une image
     */
    public function remove(WarehouseImage $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
