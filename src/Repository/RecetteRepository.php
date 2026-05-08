<?php

namespace App\Repository;

use App\Entity\CategorieRecette;
use App\Entity\Recette;
use App\Entity\TagRecette;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class RecetteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Recette::class);
    }

    public function findByFilters(
        ?string $titre,
        ?CategorieRecette $cat,
        ?string $diff,
        ?TagRecette $tag
    ): array {
        $qb = $this->createQueryBuilder('r')
            ->leftJoin('r.categorie', 'c')
            ->leftJoin('r.auteur', 'u')
            ->addSelect('c', 'u')
            ->where('r.publiee = true');

        if ($titre) {
            $qb->andWhere('r.titre LIKE :titre')
               ->setParameter('titre', '%' . $titre . '%');
        }
        if ($cat) {
            $qb->andWhere('r.categorie = :cat')
               ->setParameter('cat', $cat);
        }
        if ($diff) {
            $qb->andWhere('r.difficulte = :diff')
               ->setParameter('diff', $diff);
        }
        if ($tag) {
            $qb->innerJoin('r.tags', 't')
               ->andWhere('t = :tag')
               ->setParameter('tag', $tag);
        }

        return $qb->orderBy('r.dateCreation', 'DESC')
                  ->getQuery()
                  ->getResult();
    }

    public function findLastPublished(int $limit = 3): array
    {
        return $this->createQueryBuilder('r')
            ->where('r.publiee = true')
            ->orderBy('r.dateCreation', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findPublished(): array
    {
        return $this->createQueryBuilder('r')
            ->where('r.publiee = true')
            ->orderBy('r.dateCreation', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findPublishedQueryBuilder()
    {
        return $this->createQueryBuilder('r')
            ->where('r.publiee = true')
            ->orderBy('r.dateCreation', 'DESC');
    }

    public function countPublished(): int
    {
        return (int) $this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->where('r.publiee = true')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getRecettesParCategorie(): array
    {
        $results = $this->createQueryBuilder('r')
            ->select('c.nom as categorie, COUNT(r.id) as total')
            ->join('r.categorie', 'c')
            ->groupBy('c.nom')
            ->getQuery()
            ->getResult();

        $data = [];
        foreach ($results as $row) {
            $data[$row['categorie']] = (int) $row['total'];
        }
        return $data;
    }

    public function getMoyenneIngredients(): float
    {
        $result = $this->createQueryBuilder('r')
            ->select('AVG(SIZE(r.ingredients)) as moyenne')
            ->getQuery()
            ->getSingleScalarResult();

        return round((float) $result, 2);
    }

    public function getTopAuteurs(int $limit = 3): array
    {
        return $this->createQueryBuilder('r')
            ->select('u.pseudo, u.email, COUNT(r.id) as total')
            ->join('r.auteur', 'u')
            ->groupBy('u.id')
            ->orderBy('total', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findTopLongest(int $limit = 3): array
    {
        return $this->createQueryBuilder('r')
            ->orderBy('r.tempsPreparation + COALESCE(r.tempsCuisson, 0)', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
