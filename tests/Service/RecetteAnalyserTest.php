<?php

namespace App\Tests\Service;

use App\Entity\Recette;
use App\Repository\RecetteRepository;
use App\Service\RecetteAnalyser;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class RecetteAnalyserTest extends TestCase
{
    private RecetteAnalyser $analyser;
    private RecetteRepository&MockObject $repo;

    protected function setUp(): void
    {
        $this->repo    = $this->createMock(RecetteRepository::class);
        $this->analyser = new RecetteAnalyser($this->repo);
    }

    public function testGetTempsTotalAdditionnePreparationEtCuisson(): void
    {
        $recette = new Recette();
        $recette->setTempsPreparation(30);
        $recette->setTempsCuisson(45);
        $this->assertSame(75, $this->analyser->getTempsTotal($recette));
    }

    public function testGetTempsTotalAvecCuissonNull(): void
    {
        $recette = new Recette();
        $recette->setTempsPreparation(20);
        $recette->setTempsCuisson(null);
        $this->assertSame(20, $this->analyser->getTempsTotal($recette));
    }

    public function testGetTotalRecettesPubliees(): void
    {
        $this->repo->method('countPublished')->willReturn(5);
        $this->assertSame(5, $this->analyser->getTotalRecettesPubliees());
    }

    public function testGetMoyenneIngredientsRetourneZeroSiAucuneRecette(): void
    {
        $this->repo->method('getMoyenneIngredients')->willReturn(0.0);
        $this->assertSame(0.0, $this->analyser->getMoyenneIngredients());
    }
}