<?php

namespace App\Controller;

use App\Entity\Ingredient;
use App\Entity\Recette;
use App\Form\IngredientType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class IngredientController extends AbstractController
{
    public function __construct(private EntityManagerInterface $em) {}

    #[Route('/recettes/{id}/ingredients/nouveau', name: 'ingredient_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_CUISINIER')]
    public function new(Request $request, Recette $recette): Response
    {
        $this->denyAccessUnlessGranted('edit', $recette);

        $ingredient = new Ingredient();
        $form = $this->createForm(IngredientType::class, $ingredient);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $ingredient->setRecette($recette);
            $this->em->persist($ingredient);
            $this->em->flush();

            $this->addFlash('success', '✅ Ingrédient "' . $ingredient->getNom() . '" ajouté !');
            return $this->redirectToRoute('recette_show', ['id' => $recette->getId()]);
        }

        return $this->render('ingredient/new.html.twig', [
            'recette'    => $recette,
            'form'       => $form->createView(),
            'ingredient' => $ingredient,
        ]);
    }

    #[Route('/ingredients/{id}/supprimer', name: 'ingredient_delete', methods: ['POST'])]
    #[IsGranted('ROLE_CUISINIER')]
    public function delete(Request $request, Ingredient $ingredient): Response
    {
        $recette = $ingredient->getRecette();
        $this->denyAccessUnlessGranted('edit', $recette);

        if ($this->isCsrfTokenValid('delete-ingredient-' . $ingredient->getId(), $request->request->get('_token'))) {
            //effacer
        $this->em->remove($ingredient);
            $this->em->flush();
            $this->addFlash('success', '🗑️ Ingrédient supprimé.');
        }

        return $this->redirectToRoute('recette_show', ['id' => $recette->getId()]);
    }
}
