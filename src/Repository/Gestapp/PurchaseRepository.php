<?php

namespace App\Repository\Gestapp;

use App\Entity\Gestapp\Purchase;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Purchase|null find($id, $lockMode = null, $lockVersion = null)
 * @method Purchase|null findOneBy(array $criteria, array $orderBy = null)
 * @method Purchase[]    findAll()
 * @method Purchase[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PurchaseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Purchase::class);
    }

    /**
     * @param $user
     * @return int|mixed|string
     * Liste les recommandation reçues
     */
    public function findByUserReceiptNew($user)
    {
        return $this->createQueryBuilder('r')
            ->join('r.customer', 'm')
            ->andWhere('m.id = :id')
            ->andWhere('r.status = :status')
            ->setParameter('id', $user)
            ->setParameter('status', 'PENDING')
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
            ;
    }

    /**
     * @param $user
     * @return int|mixed|string
     * Liste les recommandation reçues lues
     */
    public function findByUserReceiptRead($user)
    {
        return $this->createQueryBuilder('r')
            ->join('r.member', 'm')
            ->andWhere('m.id = :id')
            ->andWhere('r.isRead = 0')
            ->setParameter('id', $user)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
            ;
    }

    public function findLastRef()
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.property = :property')
            ->orderBy('p.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }

    /**
     * @param $user
     * @return int|mixed|string
     * Liste les recommandation envoyées
     */
    public function findByUserSend($user)
    {
        return $this->createQueryBuilder('r')
            ->join('r.customer', 'm')
            ->andWhere('m.id = :id')
            ->setParameter('id', $user)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
            ;
    }

    /**
     * @param $user
     * @return int|mixed|string
     * Liste les recommandation envoyées lues
     */
    public function findByUserSendRead($user)
    {
        return $this->createQueryBuilder('r')
            ->join('r.author', 'm')
            ->andWhere('m.id = :id')
            ->andWhere('r.isRead = 0')
            ->setParameter('id', $user)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
            ;
    }

    /**
     * @param $user
     * @return int|mixed|string
     * Liste les recommandation reçues
     */
    public function onePurchase($commande)
    {
        return $this->createQueryBuilder('p')
            ->join('p.customer', 'm')
            ->addSelect('
                p.status as status,
                p.firstName AS firstNameL,
                p.lastName AS lastNameL,
                p.address AS addressL,
                p.complement AS complementL,
                p.zipcode AS zipcodeL,
                p.city AS cityL,
                p.numPurchase AS numPurchase,
                p.total AS total,
                m.firstName AS firstNameF,
                m.lastName AS lastNameF,
                m.adress1 AS addressF,
                m.Adress2 AS complementF,
                m.zipcode AS zipcodeF,
                m.city AS cityF
            ')
            ->andWhere('p.numPurchase = :numPurchase')
            ->setParameter('numPurchase', $commande)
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }

    // /**
    //  * @return Purchase[] Returns an array of Purchase objects
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
    public function findOneBySomeField($value): ?Purchase
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
