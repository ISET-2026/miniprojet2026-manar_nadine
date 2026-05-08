<?php

namespace App\Controller;

use App\Repository\RecetteRepository;
use App\Service\RecetteAnalyser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    public function __construct(private RecetteAnalyser $analyser) {}

    #[Route('/', name: 'app_home')]
    public function index(RecetteRepository $recetteRepo): Response
    {
        $dernieres = $recetteRepo->findLastPublished(3);

        $stats = [
            'totalPubliees'     => $this->analyser->getTotalRecettesPubliees(),
            'parCategorie'      => $this->analyser->getRecettesParCategorie(),
            'moyenneIngredients'=> $this->analyser->getMoyenneIngredients(),
        ];

        return $this->render('home/index.html.twig', [
            'dernieres' => $dernieres,
            'stats'     => $stats,
        ]);
    }
}
