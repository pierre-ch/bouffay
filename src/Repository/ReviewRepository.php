<?php

namespace App\Repository;

use App\Entity\Review;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Review>
 */
class ReviewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Review::class);
    }

    public function getAverageRating(User $seller): ?float
    {
        $result = $this->createQueryBuilder('r')
            ->select('AVG(r.rating) as avg')
            ->where('r.seller = :seller')
            ->setParameter('seller', $seller)
            ->getQuery()
            ->getSingleScalarResult();

        return $result ? round((float) $result, 1) : null;
    }

    public function hasAlreadyReviewed(User $author, User $seller): bool
    {
        return (bool) $this->findOneBy(['author' => $author, 'seller' => $seller]);
    }
}
