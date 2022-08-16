<?php

namespace App\Repository;

use App\Entity\Task;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\ResultSetMapping;

/**
 * @extends ServiceEntityRepository<Task>
 *
 * @method Task|null find($id, $lockMode = null, $lockVersion = null)
 * @method Task|null findOneBy(array $criteria, array $orderBy = null)
 * @method Task[]    findAll()
 * @method Task[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TaskRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Task::class);
    }

    public function add(Task $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Task $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

   /**
    * @return Task Returns a Task object
    */
    public function findStartedTask(): array
    {
        return $this->createQueryBuilder('t')
            // ->leftJoin('t.taskTimes', 'times')
            ->andWhere('t.status = 1')
            // ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            // ->setMaxResults(1)
            ->getQuery()
            ->getResult()
       ;
    }

    public function findElapsedTimeToday(): \DateInterval
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('total','t');
        $query = $this->getEntityManager()->createNativeQuery("
            SELECT sum(TIME_TO_SEC(timediff(end_time,start_time))) as total 
            FROM task_times where date(start_time) = date(now())    
        ",$rsm);
        $t = $query->getResult();
        if(count($t)!=1) return new \DateInterval('PT0S');     
        if($t[0]['t']=='') return new \DateInterval('PT0S');

        // t is an array which contains the number of seconds
        //var_dump: array(1) { [0]=> array(1) { ["t"]=> string(4) "6358" } }
        //we return a Dateinterval 

        $dti = new \DateInterval('PT'.$t[0]['t'].'S');
        return $dti;
    }

//    public function findOneBySomeField($value): ?Task
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
