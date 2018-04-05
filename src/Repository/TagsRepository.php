<?php

namespace App\Repository;

use App\Entity\Tag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\ORMException;
use Symfony\Bridge\Doctrine\RegistryInterface;

class TagsRepository extends ServiceEntityRepository {
    public function __construct(RegistryInterface $registry) {
        parent::__construct($registry, Tag::class);
    }

    /**
     * @param $title
     * @return null
     * @throws ORMException
     */
    public function findOneByTitleOrCreateIfNotExists($title) {
        $res = $this->createQueryBuilder('t')
            ->where('t.title = :title')->setParameter('title', $title)
            ->setMaxResults(1)
            ->getQuery()
            ->getResult();

        if (empty($res)) {
            $tag = new Tag();
            $tag->setTitle($title);
            $em = $this->getEntityManager();
            $em->persist($tag);
            return $tag;
        }

        return $res[0];
    }


    /*public function findByImageId($imageId) {
        return $this->createQueryBuilder('t')
            ->join('t.id', 'i')
            ->addSelect('i')
            ->where('i.id = :imageId')->setParameter('imageId', $imageId)
            ->getQuery()
            ->getResult();
    }*/
}
