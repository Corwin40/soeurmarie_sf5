<?php

namespace App\Repository\Gestapp;

use App\Entity\Gestapp\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Product|null find($id, $lockMode = null, $lockVersion = null)
 * @method Product|null findOneBy(array $criteria, array $orderBy = null)
 * @method Product[]    findAll()
 * @method Product[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    public function ListFilterscategories($filters)
    {
        $query = $this->createQueryBuilder('p');
        if($filters != null){
            $query
                ->leftJoin('p.productNature', 'n')
                ->leftJoin('p.producer', 'pr')
                ->leftJoin('pr.structure', 's')
                ->addSelect('
                    p.position as position,
                    p.id AS id,
                    p.name AS name, 
                    p.description AS description,
                    p.details,
                    p.price,
                    p.quantity,
                    p.productName AS productName,
                    p.ref AS ref,
                    p.isDisponible,
                    p.isStar,
                    p.isOnLine,
                    s.name AS producer,
                    n.name AS nameNature,
                    s.logoStructureName AS logoStructureName
                ')
                ->andWhere('p.ProductCategory IN(:cats)')
                ->setParameter(':cats', array_values($filters));
        }else{

        }
        return $query->getQuery()->getResult();
    }

    /**
     * @param $filter
     * @return float|int|mixed|string
     */
    public function ListFilterscategory($filter)
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.productNature', 'n')
            ->leftJoin('p.producer', 'pr')
            ->leftJoin('pr.structure', 's')
            ->addSelect('
                p.position as position,
                p.id AS id,
                p.name AS name, 
                p.description AS description,
                p.details,
                p.price,
                p.quantity,
                p.productName AS productName,
                p.ref AS ref,
                p.isDisponible,
                p.isStar,
                p.isOnLine,
                s.name AS producer,
                n.name AS nameNature,
                s.logoStructureName AS logoStructureName
            ')
            ->andWhere('p.ProductCategory = :cat')
            ->setParameter(':cat', $filter)
            ->getQuery()
            ->getResult()
            ;
    }

    // /**
    //  * @return Product[] Returns an array of Product objects
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
    public function findOneBySomeField($value): ?Product
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    /**
    * Liste les produits selon une nature (id).
    * @return Product[] Returns an array of Product objects
    */
    public function oneNature($idnat)
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.producer', 'pr')
            ->leftJoin('pr.structure', 's')
            ->leftJoin('p.productNature', 'n')
            ->leftJoin('p.ProductCategory', 'c')
            ->leftJoin('p.productUnit', 'pu')
            ->Select('
                p.position as pos,
                p.id AS id,
                p.name AS name, 
                p.description AS description,
                p.details,
                p.price,
                pu.name AS productUnit,
                p.quantity,
                p.productName AS productName,
                n.id AS idNature,
                p.ref AS ref,
                n.name AS nameNature,
                c.name AS nameCategory,
                p.isDisponible,
                p.isStar,
                p.isOnLine,
                s.name AS producer,
                s.logoStructureName AS logoStructureName
                 ')
            ->andWhere('n.id = :idnat')
            ->andWhere('p.isOnLine = :isOnLine')
            ->setParameter('idnat', $idnat)
            ->setParameter('isOnLine', 1)
            ->orderBy('p.position', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Liste les produits selon une nature name.
     * @return Product[] Returns an array of Product objects
     */
    public function oneNatureName($nature)
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.producer', 'pr')
            ->leftJoin('pr.structure', 's')
            ->leftJoin('p.productNature', 'n')
            ->leftJoin('p.productUnit', 'pu')
            ->Select('
                p.position as position,
                p.id AS id,
                p.name AS name, 
                p.description AS description,
                p.details,
                p.price,
                pu.name AS productUnit,
                p.quantity,
                p.productName AS productName,
                n.id AS idNature,
                p.ref AS ref,
                n.name AS nameNature,
                p.isDisponible,
                p.isStar,
                p.isOnLine,
                s.name AS producer,
                s.logoStructureName AS logoStructureName
                 ')
            ->andWhere('n.name = :nat')
            ->andWhere('p.isOnLine = :isOnLine')
            ->setParameter('nat', $nature)
            ->setParameter('isOnLine', 1)
            ->orderBy('p.id', 'ASC')
            ->getQuery()
            ->getResult()
            ;
    }

    /**
     * Liste les produits selon une categorie et ses enfants.
     * @return Product[] Returns an array of Product objects
     */
    public function oneCategory($idcat)
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.producer', 'pr')
            ->leftJoin('pr.structure', 's')
            ->leftJoin('p.ProductCategory', 'c')
            ->leftJoin('p.productUnit', 'pu')
            ->leftJoin('p.productNature', 'n')
            ->Select('
                
                p.position as position,
                p.id AS id,
                p.name AS name, 
                p.description AS description,
                p.details,
                p.price,
                pu.name AS productUnit,
                p.quantity,
                p.productName AS productName,
                n.name AS productNature,
                c.id AS idCategory,
                p.ref AS ref,
                c.name AS nameCategory,
                p.isDisponible,
                p.isStar,
                p.isOnLine,
                s.name AS producer,
                s.logoStructureName AS logoStructureName
                 ')
            ->andWhere('c.id = :idcat')
            ->andWhere('p.isOnLine = :isOnLine')
            ->setParameter('idcat', $idcat)
            ->setParameter('isOnLine', 1)
            ->orderBy('p.id', 'ASC')
            ->getQuery()
            ->getResult()
            ;
    }

    /**
     * Liste tous les produits en ligne.
     * @return Product[] Returns an array of Product objects
     */
    public function listALlProduct()
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.producer', 'pr')
            ->leftJoin('pr.structure', 's')
            ->leftJoin('p.productNature', 'n')
            ->leftJoin('p.ProductCategory', 'c')
            ->Select('
                p.position as position,
                p.id AS id,
                p.name AS name, 
                p.description AS description,
                p.details,
                p.price,
                p.quantity,
                p.productName AS productName,
                n.id AS idNature,
                n.name AS nameNature,
                c.name AS nameCategory,
                p.isDisponible,
                p.isStar,
                p.isOnLine,
                s.name AS producer,
                s.logoStructureName AS logoStructureName
                 ')
            ->andWhere('p.isOnLine = :isOnLine')
            ->setParameter('isOnLine', 1)
            ->orderBy('p.id', 'ASC')
            ->getQuery()
            ->getResult()
            ;
    }

    /**
     * Liste les produits selon une categorie et ses enfants.
     * @return Product[] Returns an array of Product objects
     */
    public function childCategory($findchild)
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.producer', 'pr')
            ->leftJoin('pr.structure', 's')
            ->leftJoin('p.ProductCategory', 'c')
            ->leftJoin('p.productUnit', 'pu')
            ->leftJoin('p.productNature', 'n')
            ->Select('
                p.position as position,
                p.id AS id,
                p.name AS name, 
                p.description AS description,
                p.details,
                p.price,
                pu.name AS productUnit,
                p.quantity,
                p.productName AS productName,
                n.name AS nameNature,
                c.id AS idCategory,
                p.ref AS ref,
                c.name AS nameCategory,
                p.isDisponible,
                p.isStar,
                p.isOnLine,
                s.name AS producer,
                s.logoStructureName AS logoStructureName
                 ')
            ->andWhere('c.id in (:childs)')
            ->andWhere('p.isOnLine = :isOnLine')
            ->setParameter('childs', $findchild)
            ->setParameter('isOnLine', 1)
            ->orderBy('p.id', 'ASC')
            ->getQuery()
            ->getResult()
            ;
    }

    /**
     * Liste les derniers produits d'une nature.
     * @return Product[] Returns an array of Product objects
     */
    public function lastProduct($idnat)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.productNature = :productNature')
            ->setParameter('productNature', $idnat)
            ->orderBy('p.id', 'DESC')
            ->setMaxResults(4)
            ->getQuery()
            ->getResult()
            ;
    }

    public function searchProduct($word){
        $query = $this->createQueryBuilder('p');
        //$query->where('p.isOnLine = 1');
        if($word != null){
            $query
                ->andWhere('MATCH_AGAINST(p.name, p.description, p.ref) AGAINST (:word boolean)>0')
                ->setParameter('word', $word)
            ;
        }
        return $query->getQuery()->getResult();
    }

    /**
     * Liste les derniers produits d'une nature.
     * @return Product[] Returns an array of Product objects
     */
    public function favoriesproducts()
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.isStar = 1')
            ->orderBy('p.id', 'DESC')
            ->setMaxResults(4)
            ->getQuery()
            ->getResult()
            ;
    }
}
