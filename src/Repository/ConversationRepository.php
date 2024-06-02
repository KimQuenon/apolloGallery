<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Conversation;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Conversation>
 *
 * @method Conversation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Conversation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Conversation[]    findAll()
 * @method Conversation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ConversationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Conversation::class);
    }

    /**
     * sort conversations based on the most recent messages
     *
     * @return array
     */
    public function sortConvByRecentMsg($user)
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.messages', 'm')
            ->andWhere('c.user = :user')
            ->setParameter('user', $user)
            ->orderBy('m.timestamp', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Display conversations (expert)
     *
     * @param User $expert
     * @return array
     */
    public function findConversationsByExpert(User $expert): array
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.messages', 'm')
            ->andWhere('c.expert = :expert')
            ->setParameter('expert', $expert)
            ->orderBy('m.timestamp', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Display conversations by user
     *
     * @param [type] $slug
     * @param User $user
     * @return void
     */
    public function findConversationBySlugAndUser($slug, User $user)
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.user', 'u')
            ->leftJoin('c.expert', 'e')
            ->andWhere('u.slug = :slug OR e.slug = :slug')
            ->setParameter('slug', $slug)
            ->andWhere('u = :user OR e = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getOneOrNullResult();
    }

    //    /**
    //     * @return Conversation[] Returns an array of Conversation objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('c.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Conversation
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
