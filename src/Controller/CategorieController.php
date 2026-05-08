<?php

namespace App\Controller;

use App\Entity\CategorieRecette;
use App\Form\CategorieRecetteType;
use App\Repository\CategorieRecetteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/categories')]
#[IsGranted('ROLE_ADMIN')]
class CategorieController extends AbstractController
{
    public function __construct(private EntityManagerInterface $em) {}

    #[Route('', name: 'categorie_index', methods: ['GET', 'POST'])]
    public function index(Request $request, CategorieRecetteRepository $repo): Response
    {
        $categorie = new CategorieRecette();
        $form = $this->createForm(CategorieRecetteType::class, $categorie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($categorie);
            $this->em->flush();
            $this->addFlash('success', '✅ Catégorie "' . $categorie->getNom() . '" créée !');
            return $this->redirectToRoute('categorie_index');
        }

        return $this->render('category/index.html.twig', [
            'categories' => $repo->findAll(),
            'form'       => $form->createView(),
        ]);
    }
}
