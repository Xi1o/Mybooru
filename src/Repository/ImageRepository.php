<?php

namespace App\Repository;

use App\Entity\Image;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bridge\Doctrine\RegistryInterface;

class ImageRepository extends ServiceEntityRepository {
    public function __construct(RegistryInterface $registry) {
        parent::__construct($registry, Image::class);
    }

    public function findByAuthor($idAuthor) {
        return $this->createQueryBuilder('i')
            ->where('i.author = :author')->setParameter('author', $idAuthor)
            ->getQuery()
            ->getResult();
    }

    public function findByTags($tags, $limit, $offset) {
        $res = $this->createQueryBuilder('i')
            ->innerJoin('i.tags', 't')
            ->where('t.title IN(:tags)')->setParameter('tags', $tags)
            ->orderBy('i.created', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->distinct();

        return new Paginator($res);
    }

    /**
     * @param $tags
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function countHasTags($tags) {
        return $this->createQueryBuilder('i')
            ->select('COUNT(DISTINCT i.id)')
            ->innerJoin('i.tags', 't')
            ->where('t.title IN(:tags)')->setParameter('tags', $tags)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
