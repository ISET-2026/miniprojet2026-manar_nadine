<?php

namespace App\Service;

use App\Entity\Recette;
use App\Repository\RecetteRepository;

class RecetteAnalyser
{
    public function __construct(private RecetteRepository $repo) {}

    public function getTempsTotal(Recette $r): int
    {
        return ($r->getTempsPreparation() ?? 0) + ($r->getTempsCuisson() ?? 0);
    }

    public function getTotalRecettesPubliees(): int
    {
        return $this->repo->countPublished();
    }

    public function getRecettesParCategorie(): array
    {
        return $this->repo->getRecettesParCategorie();
    }

    public function getMoyenneIngredients(): float
    {
        return $this->repo->getMoyenneIngredients();
    }
}
