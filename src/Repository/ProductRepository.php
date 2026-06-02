<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    public function findByFilters(array $filters): array
    {
        $qb = $this->createQueryBuilder('p')
            ->andWhere('p.status = :status')
            ->setParameter('status', 'active')
            ->leftJoin('p.category', 'c')
            ->leftJoin('p.tags', 't')
            ->addSelect('c', 't');

        if (!empty($filters['name'])) {
            $qb->andWhere('LOWER(p.name) LIKE LOWER(:name)')
               ->setParameter('name', '%' . $filters['name'] . '%');
        }

        if (!empty($filters['category'])) {
            $qb->andWhere('p.category = :category')
               ->setParameter('category', $filters['category']);
        }

        if (!empty($filters['tags']) && count($filters['tags']) > 0) {
            foreach ($filters['tags'] as $i => $tag) {
                $qb->andWhere(":tag_$i MEMBER OF p.tags")
                   ->setParameter("tag_$i", $tag);
            }
        }

        if (!empty($filters['minPrice'])) {
            $qb->andWhere('p.price >= :minPrice')
               ->setParameter('minPrice', $filters['minPrice']);
        }

        if (!empty($filters['maxPrice'])) {
            $qb->andWhere('p.price <= :maxPrice')
               ->setParameter('maxPrice', $filters['maxPrice']);
        }

        if (!empty($filters['expiresAtAfter'])) {
            $qb->andWhere('p.expiresAt >= :expiresAtAfter')
               ->setParameter('expiresAtAfter', $filters['expiresAtAfter']);
        }

        if (!empty($filters['expiresAtBefore'])) {
            $qb->andWhere('p.expiresAt <= :expiresAtBefore')
               ->setParameter('expiresAtBefore', $filters['expiresAtBefore']);
        }

        $sort = $filters['sort'] ?? 'trending';

        if ($sort === 'trending') {
            $qb->orderBy('p.soldCount', 'DESC')
               ->addOrderBy('p.createdAt', 'DESC');
        } else {
            match ($sort) {
                'date_asc'    => $qb->orderBy('p.createdAt', 'ASC'),
                'price_asc'   => $qb->orderBy('p.price', 'ASC'),
                'price_desc'  => $qb->orderBy('p.price', 'DESC'),
                'name_asc'    => $qb->orderBy('p.name', 'ASC'),
                'name_desc'   => $qb->orderBy('p.name', 'DESC'),
                'expires_asc' => $qb->orderBy('p.expiresAt', 'ASC'),
                default       => $qb->orderBy('p.createdAt', 'DESC'),
            };
        }

        return $qb->getQuery()->getResult();
    }

    public function getTotalStockValue(): float
    {
        $result = $this->createQueryBuilder('p')
            ->select('SUM(p.price * p.stock) as total_value')
            ->getQuery()
            ->getSingleScalarResult();

        return (float) $result;
    }
}
