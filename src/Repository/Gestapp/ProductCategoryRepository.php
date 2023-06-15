<?php

namespace App\Repository\Gestapp;

use App\Entity\Gestapp\ProductCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ProductCategory|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProductCategory|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProductCategory[]    findAll()
 * @method ProductCategory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductCategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductCategory::class);
    }


    public function findParents()
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.parent IS NULL')
            ->getQuery()
            ->getResult()
            ;
    }


    public function findChilds($idcat)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.parent = :idcat')
            ->setParameter('idcat', $idcat)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Liste les catégories selon la nature
     */
    public function CatByNat()
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.Nature', 'n')
            ->leftJoin('c.parent', 'p')
            ->select('
                p.id as parentCat,
                n.name as natureName,
                n.id as natureId,
                c.name as nameCat,
                c.id as categoryId
            ')
            ->getQuery()
            ->getResult()
            ;
    }

    /**
     * Liste les catégories selon la nature pour select
     */
    public function SelectCategories($nature)
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.Nature', 'n')
            ->leftJoin('c.parent', 'p')
            ->select('
                p.id as parentCat,
                n.name as natureName,
                n.id as natureId,
                c.name as nameCat,
                c.id as categoryId
            ')
            ->andWhere('n.id = :idnat')
            ->setParameter('idnat', $nature)
            ->getQuery()
            ->getResult()
            ;
    }
}
