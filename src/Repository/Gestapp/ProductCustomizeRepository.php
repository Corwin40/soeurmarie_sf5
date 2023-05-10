<?php

namespace App\Repository\Gestapp;

use App\Entity\Gestapp\ProductCustomize;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ProductCustomize|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProductCustomize|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProductCustomize[]    findAll()
 * @method ProductCustomize[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductCustomizeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductCustomize::class);
    }

    public function findLastProductCustomize($idproduct)
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.product', 'p')
            ->leftJoin('c.format', 'f')
            ->addSelect('
                p.id as idproduct,
                p.name as product,
                f.weight as weight,
                f.id as idformat,
                f.priceformat as priceformat,
                f.length as lenght,
                f.height as height,
                f.name as format,
                c.uuid as uuid,
                c.name as name,
                c.id as id                
            ')
            ->andWhere('p.id = :idproduct')
            ->setParameter('idproduct', $idproduct)
            ->orderBy('c.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }

    public function findCart($idproduct, $uuid, $item)
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.product', 'p')
            ->leftJoin('c.format', 'f')
            ->addSelect('
                p.id as idproduct,
                p.name as product,
                f.weight as weight,
                f.id as idformat,
                f.priceformat as priceformat,
                f.length as lenght,
                f.height as height,
                f.name as format,
                c.uuid as uuid,
                c.name as name,
                c.id as id                
            ')
            ->andWhere('p.id = :idproduct')
            ->setParameter('idproduct', $idproduct)
            ->andWhere('c.uuid = :uuid')
            ->setParameter('uuid', $uuid)
            ->andWhere('c.item = :item')
            ->setParameter('item', $item)
            ->orderBy('c.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }

    // /**
    //  * @return ProductCustomize[] Returns an array of ProductCustomize objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ProductCustomize
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
