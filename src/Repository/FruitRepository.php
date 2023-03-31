<?php

namespace App\Repository;

use App\Entity\Fruit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Fruit>
 *
 * @method Fruit|null find($id, $lockMode = null, $lockVersion = null)
 * @method Fruit|null findOneBy(array $criteria, array $orderBy = null)
 * @method Fruit[]    findAll()
 * @method Fruit[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FruitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Fruit::class);
    }

    public function save(Fruit $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Fruit $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

   /**
    * @return Fruit[] Returns an array of Fruit objects
    */
   public function findByNameAndFamily($name, $family, $limit, $offset): array
   {
        $qb = $this->createQueryBuilder('f');
        $countQb = $this->createQueryBuilder('f')->select('count(f.id)');
        if ($name) {
            $qb->andWhere('f.name LIKE :name')->setParameter('name', '%'.$name.'%');
            $countQb->andWhere('f.name LIKE :name')->setParameter('name', '%'.$name.'%');
        }

        if ($family) {
            $qb->andWhere('f.family LIKE :family')->setParameter('family', '%'.$family.'%');
            $countQb->andWhere('f.family LIKE :family')->setParameter('family', '%'.$family.'%');
        }

        if ($limit) $qb->setMaxResults($limit);

        if ($offset) $qb->setFirstResult($offset);

        return [
            $countQb->getQuery()->getSingleScalarResult(),
            $qb->getQuery()->getResult()
        ];
   }
}
