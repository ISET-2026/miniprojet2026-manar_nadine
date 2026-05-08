<?php

namespace App\Command;

use App\Repository\CategorieRecetteRepository;
use App\Repository\IngredientRepository;
use App\Repository\RecetteRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:recipehub:stats',
    description: 'Affiche les statistiques de la plateforme de recettes',
)]
class RecipeHubStatsCommand extends Command
{
    public function __construct(
        private RecetteRepository $recetteRepo,
        private CategorieRecetteRepository $categorieRepo,
        private IngredientRepository $ingredientRepo,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('detail', null, InputOption::VALUE_NONE,   'Affiche le détail par catégorie')
            ->addOption('top',    null, InputOption::VALUE_REQUIRED,'Affiche le top N des recettes les plus longues', 3);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('📊 Statistiques RecipeHub');

        // ─── Recettes ────────────────────────────────────────────────────────
        $total     = $this->recetteRepo->count([]);
        $publiees  = $this->recetteRepo->countPublished();
        $brouillons= $total - $publiees;

        $io->section('Recettes');
        $io->table(
            ['Statut', 'Nombre'],
            [
                ['✅ Publiées',   $publiees],
                ['📝 Brouillons', $brouillons],
                ['📦 Total',      $total],
            ]
        );

        // ─── Ingrédients ─────────────────────────────────────────────────────
        $totalIngredients = $this->ingredientRepo->countAll();
        $io->section('Ingrédients');
        $io->writeln("  Total : <info>$totalIngredients</info> ingrédients enregistrés");

        // ─── Répartition par catégorie ───────────────────────────────────────
        if ($input->getOption('detail')) {
            $io->section('Répartition par catégorie');
            $parCategorie = $this->recetteRepo->getRecettesParCategorie();
            $rows = [];
            foreach ($parCategorie as $cat => $nb) {
                $rows[] = [$cat, $nb];
            }
            $io->table(['Catégorie', 'Nb recettes'], $rows);
        }

        // ─── Répartition par difficulté ───────────────────────────────────────
        $io->section('Répartition par difficulté');
        $facile   = $this->recetteRepo->count(['difficulte' => 'facile']);
        $moyen    = $this->recetteRepo->count(['difficulte' => 'moyen']);
        $difficile= $this->recetteRepo->count(['difficulte' => 'difficile']);
        $io->table(
            ['Difficulté', 'Nombre'],
            [['🟢 Facile', $facile], ['🟡 Moyen', $moyen], ['🔴 Difficile', $difficile]]
        );

        // ─── Top N recettes les plus longues ─────────────────────────────────
        $topN = (int) $input->getOption('top');
        $io->section("Top $topN des recettes les plus longues");
        $topRecettes = $this->recetteRepo->findTopLongest($topN);
        $rows = [];
        foreach ($topRecettes as $r) {
            $rows[] = [
                $r->getTitre(),
                $r->getTempsPreparation() . ' min',
                ($r->getTempsCuisson() ?? 0) . ' min',
                $r->getTempsTotal() . ' min',
            ];
        }
        $io->table(['Titre', 'Préparation', 'Cuisson', 'Total'], $rows);

        // ─── Top auteurs ──────────────────────────────────────────────────────
        $io->section('Top 3 des auteurs les plus prolifiques');
        $topAuteurs = $this->recetteRepo->getTopAuteurs(3);
        $rows = [];
        foreach ($topAuteurs as $a) {
            $rows[] = [$a['pseudo'] ?? $a['email'], $a['total']];
        }
        $io->table(['Auteur', 'Nb recettes'], $rows);

        $io->success('Statistiques générées avec succès !');
        return Command::SUCCESS;
    }
}
