<?php

namespace App\Repository\Webapp;

use App\Entity\Webapp\Page;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Page|null find($id, $lockMode = null, $lockVersion = null)
 * @method Page|null findOneBy(array $criteria, array $orderBy = null)
 * @method Page[]    findAll()
 * @method Page[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Page::class);
    }

    /**
     * Liste les pages qui s'afficheront dans le bloc menu.
     */
    public function listMenu()
    {
        return $this->createQueryBuilder('p')
            ->select('p.id, p.name, p.slug, p.state, p.isMenu, p.isPublish, p.position, pa.id AS parent')
            ->leftJoin('p.parent', 'pa')
            ->andWhere('p.state = :state')
            ->andWhere('p.isMenu = :isMenu')
            ->andWhere('p.isPublish = :isPublish')
            ->setParameter('state', 'finished')
            ->setParameter('isMenu', 1)
            ->setParameter('isPublish', 1)
            ->orderBy('p.position', 'ASC')
            ->getQuery()
            ->getResult()
            ;
    }

    /**
     * Liste les pages qui s'afficheront dans le bloc menu.
     */
    public function listpage()
    {
        return $this->createQueryBuilder('p')
            ->select('p.id, p.name, p.slug, p.state, p.isMenu, p.isPublish, p.position, pa.id AS parent')
            ->leftJoin('p.parent', 'pa')
            ->andWhere('p.state = :state')
            ->andWhere('p.isPublish = :isPublish')
            ->andWhere('p.parent is null')
            ->setParameter('state', 'finished')
            ->setParameter('isPublish', 1)
            ->orderBy('p.position', 'ASC')
            ->getQuery()
            ->getResult()
            ;
    }


    /**
     * liste les pages par position ascendante
     */
    public function sortPosition(){
        return $this->createQueryBuilder('p')
            ->orderBy('p.position', 'ASC')
            ->getQuery()
            ->getResult()
            ;
    }

    /**
     * Liste la page qui s'affichera selon son slug.
     */
    public function findbyslug($slug)
    {
        return $this->createQueryBuilder('p')
            ->select('p.id, p.name, p.slug, p.state, p.isMenu, p.isPublish, p.position')
            ->andWhere('p.slug = :slug')
            ->andWhere('p.isMenu = :isMenu')
            ->andWhere('p.isPublish = :isPublish')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }

    /***
     * Récupère la page antérieur selon une position identique
     */
    public function previousPage($id, $position)
    {
        return $this->createQueryBuilder('p')
            ->where('p.position = :position')
            ->andWhere('p.id != :id')
            ->setParameter('position', $position)
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }

    /***
     * Récupère la page antérieur selon une position identique
     */
    public function nextPage($id, $position)
    {
        return $this->createQueryBuilder('p')
            ->where('p.position = :position')
            //->andWhere('p.id != :id')
            ->setParameter('position', $position)
            //->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }

    // /**
    //  * @return Page[] Returns an array of Page objects
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
    public function findOneBySomeField($value): ?Page
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
