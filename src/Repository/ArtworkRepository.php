<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Artwork;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Artwork>
 *
 * @method Artwork|null find($id, $lockMode = null, $lockVersion = null)
 * @method Artwork|null findOneBy(array $criteria, array $orderBy = null)
 * @method Artwork[]    findAll()
 * @method Artwork[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArtworkRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Artwork::class);
    }

    public function findByTitleOrArtistQuery(string $term): QueryBuilder
    {
        return $this->createQueryBuilder('a')
            ->where('a.title LIKE :term OR a.artistName LIKE :term OR a.artistSurname LIKE :term')  // Rechercher par titre ou nom de l'artiste
            ->setParameter('term', '%' . $term . '%');
    }
    
    public function findLatestArtworksByUser(User $user, int $limit = 4): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.author = :user')
            ->andWhere('a.archived = false')
            ->setParameter('user', $user)
            ->orderBy('a.endDate', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findArtworkUserDesc(User $user): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.author = :user')
            ->andWhere('a.archived = false')
            ->setParameter('user', $user)
            ->orderBy('a.endDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findArchivedArtworksByUser(User $user)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.author = :user')
            ->andWhere('a.archived = true')
            ->setParameter('user', $user)
            ->orderBy('a.endDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    //    /**
    //     * @return Artwork[] Returns an array of Artwork objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('a.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Artwork
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
