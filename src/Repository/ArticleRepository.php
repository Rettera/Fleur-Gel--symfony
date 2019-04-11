<?php

namespace App\Repository;

use App\Entity\Article;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Article|null find($id, $lockMode = null, $lockVersion = null)
 * @method Article|null findOneBy(array $criteria, array $orderBy = null)
 * @method Article[]    findAll()
 * @method Article[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Article::class);
    }

    /**
    *@param $title
    * @return Article[]    
    */
    
     public function findLike($title)
    {
        return $this //Recherche
            ->createQueryBuilder('a')
            ->where('a.title LIKE :title')
            ->setParameter( 'title', "%$title%")
            ->orderBy('a.createdAt', 'DESC')
            ->getQuery()
            ->execute()
            ;
    }
    public function orderByDate() //tri date
    {
        return $this->createQueryBuilder('a')
            ->orderBy('a.createdAt', 'DESC')
            ->setMaxResults(15)
            ->getQuery()
            ->getResult()
        ;
    }
     public function FiltreCateg($category)
    {
        return $this //filtrer par category
            ->createQueryBuilder('a')
            ->where('a.category = :category')
            ->setParameter( 'category', "$category")
            ->orderBy('a.createdAt', 'DESC')
            ->getQuery()
            ->execute()
            ;
    }
    public function FiltreNom($Auteur)
    {
        return $this //filtrer par category
            ->createQueryBuilder('a')
            ->where('a.Auteur = :Auteur')
            ->setParameter( 'Auteur', "$Auteur")
            ->orderBy('a.createdAt', 'DESC')
            ->getQuery()
            ->execute()
            ;
    }
    /*
    public function findOneBySomeField($value): ?Article
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
