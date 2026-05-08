<?php

namespace App\Controller;

use App\Entity\TagRecette;
use App\Form\TagRecetteType;
use App\Repository\TagRecetteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/tags')]
#[IsGranted('ROLE_ADMIN')]
class TagController extends AbstractController
{
    public function __construct(private EntityManagerInterface $em) {}

    #[Route('', name: 'tag_index', methods: ['GET', 'POST'])]
    public function index(Request $request, TagRecetteRepository $repo): Response
    {
        $tag = new TagRecette();
        $form = $this->createForm(TagRecetteType::class, $tag);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($tag);
            $this->em->flush();
            $this->addFlash('success', '✅ Tag "' . $tag->getNom() . '" créé !');
            return $this->redirectToRoute('tag_index');
        }

        return $this->render('tag/index.html.twig', [
            'tags' => $repo->findAll(),
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/supprimer', name: 'tag_delete', methods: ['POST'])]
    public function delete(Request $request, TagRecette $tag): Response
    {
        if ($this->isCsrfTokenValid('delete-tag-' . $tag->getId(), $request->request->get('_token'))) {
            $this->em->remove($tag);
            $this->em->flush();
            $this->addFlash('success', '🗑️ Tag supprimé.');
        }
        return $this->redirectToRoute('tag_index');
    }
}
