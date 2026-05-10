<?php

namespace App\Controller;

use App\Entity\Recette;
use App\Form\RecetteSearchType;
use App\Form\RecetteType;
use App\Repository\RecetteRepository;
use App\Service\FileUploader;
use App\Service\RecetteAnalyser;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;

#[Route('/recettes')]
class RecetteController extends AbstractController
{
    public function __construct(
        private RecetteAnalyser $analyser,
        private EntityManagerInterface $em,
    ) {}

    // ─── Liste avec recherche et pagination ───────────────────────────────────

    #[Route('', name: 'recette_index', methods: ['GET'])]
    public function index(
        Request $request,
        RecetteRepository $repo,
        PaginatorInterface $paginator,
    ): Response {
        $form = $this->createForm(RecetteSearchType::class, null, ['method' => 'GET']);
        $form->handleRequest($request);

        $data = $form->getData() ?? [];
        $titre     = $data['titre']      ?? null;
        $categorie = $data['categorie']  ?? null;
        $difficulte= $data['difficulte'] ?? null;
        $tag       = $data['tag']        ?? null;

        if ($titre || $categorie || $difficulte || $tag) {
            $recettes   = $repo->findByFilters($titre, $categorie, $difficulte, $tag);
            $pagination = $paginator->paginate($recettes, $request->query->getInt('page', 1), 9);
        } else {
            $qb         = $repo->findPublishedQueryBuilder();
            $pagination = $paginator->paginate($qb, $request->query->getInt('page', 1), 9);
        }

        return $this->render('recipe/index.html.twig', [
            'pagination' => $pagination,
            'searchForm' => $form->createView(),
        ]);
    }

    // ─── Détail ───────────────────────────────────────────────────────────────

    #[Route('/{id}', name: 'recette_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(Recette $recette, RequestStack $requestStack): Response
    {
        $session  = $requestStack->getSession();
        $favoris  = $session->get('favoris', []);
        $isFavori = in_array($recette->getId(), $favoris);

        return $this->render('recipe/show.html.twig', [
            'recette'    => $recette,
            'tempsTotal' => $this->analyser->getTempsTotal($recette),
            'isFavori'   => $isFavori,
        ]);
    }

    // ─── Créer ────────────────────────────────────────────────────────────────

    #[Route('/nouvelle', name: 'recette_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_CUISINIER')]
    public function new(
        Request $request,
        FileUploader $uploader,
        MailerInterface $mailer,
    ): Response {
        $recette = new Recette();
        $form    = $this->createForm(RecetteType::class, $recette);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Upload image
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                $recette->setImageName($uploader->upload($imageFile));
            }

            // Auteur
            $recette->setAuteur($this->getUser());

            // Persist ingrédients
            foreach ($recette->getIngredients() as $ingredient) {
                $ingredient->setRecette($recette);
                $this->em->persist($ingredient);
            }

            $this->em->persist($recette);
            $this->em->flush();

            // Email si publiée
            if ($recette->isPubliee()) {
                $this->sendNewRecetteEmail($mailer, $recette);
            }

            $this->addFlash('success', '🎉 Recette "' . $recette->getTitre() . '" créée avec succès !');
            return $this->redirectToRoute('recette_show', ['id' => $recette->getId()]);
        }

        return $this->render('recipe/new.html.twig', [
            'recette' => $recette,
            'form'    => $form->createView(),
        ]);
    }

    // ─── Modifier ─────────────────────────────────────────────────────────────

    #[Route('/{id}/modifier', name: 'recette_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Recette $recette,
        FileUploader $uploader,
        MailerInterface $mailer,
    ): Response {
        $this->denyAccessUnlessGranted('edit', $recette);

        $wasPubliee = $recette->isPubliee();
        $form       = $this->createForm(RecetteType::class, $recette);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Upload nouvelle image
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                if ($recette->getImageName()) {
                    $uploader->remove($recette->getImageName());
                }
                $recette->setImageName($uploader->upload($imageFile));
            }

            // Persist ingrédients
            foreach ($recette->getIngredients() as $ingredient) {
                $ingredient->setRecette($recette);
                $this->em->persist($ingredient);
            }

            $this->em->flush();

            // Email si nouvellement publiée
            if (!$wasPubliee && $recette->isPubliee()) {
                $this->sendNewRecetteEmail($mailer, $recette);
            }

            $this->addFlash('success', '✅ Recette modifiée avec succès !');
            return $this->redirectToRoute('recette_show', ['id' => $recette->getId()]);
        }

        return $this->render('recipe/edit.html.twig', [
            'recette' => $recette,
            'form'    => $form->createView(),
        ]);
    }

    // ─── Supprimer ────────────────────────────────────────────────────────────

    #[Route('/{id}/supprimer', name: 'recette_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        Recette $recette,
        FileUploader $uploader,
    ): Response {
        $this->denyAccessUnlessGranted('delete', $recette);

        if ($this->isCsrfTokenValid('delete' . $recette->getId(), $request->request->get('_token'))) {
            if ($recette->getImageName()) {
                $uploader->remove($recette->getImageName());
            }
            $this->em->remove($recette);
            $this->em->flush();
            $this->addFlash('success', '🗑️ Recette supprimée.');
        }

        return $this->redirectToRoute('recette_index');
    }

    // ─── Favoris toggle ───────────────────────────────────────────────────────

    #[Route('/{id}/favori/toggle', name: 'recette_favori_toggle', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function toggleFavori(Recette $recette, RequestStack $requestStack): Response
    {
        $session = $requestStack->getSession();
        $favoris = $session->get('favoris', []);
        $id      = $recette->getId();

        if (in_array($id, $favoris)) {
            $favoris = array_values(array_filter($favoris, fn($f) => $f !== $id));
            $this->addFlash('info', '❌ Retiré des favoris.');
        } else {
            $favoris[] = $id;
            $this->addFlash('success', '⭐ Ajouté aux favoris !');
        }

        $session->set('favoris', $favoris);
        return $this->redirectToRoute('recette_show', ['id' => $id]);
    }

    // ─── Page favoris ─────────────────────────────────────────────────────────

    #[Route('/mes-favoris', name: 'recette_favoris', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function mesFavoris(
        Request $request,
        RequestStack $requestStack,
        RecetteRepository $repo,
        PaginatorInterface $paginator,
    ): Response {
        $session    = $requestStack->getSession();
        $favorisIds = $session->get('favoris', []);

        $recettes = [];
        if (!empty($favorisIds)) {
            $recettes = $repo->findBy(['id' => $favorisIds]);
        }

        $pagination = $paginator->paginate($recettes, $request->query->getInt('page', 1), 6);

        return $this->render('recipe/favoris.html.twig', [
            'pagination' => $pagination,
        ]);
    }
    // ─── Envoi email ──────────────────────────────────────────────────────────
    private function sendNewRecetteEmail(MailerInterface $mailer, Recette $recette): void
    {
        try {
            $email = (new TemplatedEmail())
                ->from('noreply@recipehub.com')
                ->to($recette->getAuteur()?->getEmail() ?? 'admin@recipehub.com')
                ->subject('🍽️ Nouvelle recette : ' . $recette->getTitre())
                ->htmlTemplate('emails/nouvelle_recette.html.twig')
                ->context(['recette' => $recette]);
            $mailer->send($email);
        } catch (\Exception $e) {
            // Ne pas bloquer si l'email échoue
        }
    }
}