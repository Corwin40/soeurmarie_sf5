<?php

namespace App\Repository\Gestapp;

use App\Entity\Gestapp\PurchaseItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PurchaseItem|null find($id, $lockMode = null, $lockVersion = null)
 * @method PurchaseItem|null findOneBy(array $criteria, array $orderBy = null)
 * @method PurchaseItem[]    findAll()
 * @method PurchaseItem[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PurchaseItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PurchaseItem::class);
    }

    /**
     * @param $value
     * @return float|int|mixed|string
     * Liste les élements composant une facture
     */
    public function itemsPurchase($num)
    {
        return $this->createQueryBuilder('i')
            ->join('i.purchase', 'p')
            ->andWhere('i.purchase = :item')
            ->setParameter('item', $num)
            ->orderBy('i.id', 'ASC')
            ->getQuery()
            ->getResult()
            ;
    }


    // /**
    //  * @return PurchaseItem[] Returns an array of PurchaseItem objects
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
    public function findOneBySomeField($value): ?PurchaseItem
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
