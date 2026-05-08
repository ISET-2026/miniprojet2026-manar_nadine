<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class RecipeHubExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('time_ago',            [$this, 'timeAgo']),
            new TwigFilter('cooking_time_format',  [$this, 'cookingTimeFormat']),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('difficulty_stars', [$this, 'difficultyStars'], ['is_safe' => ['html']]),
        ];
    }

    // ─── Filtre time_ago ──────────────────────────────────────────────────────

    public function timeAgo(?\DateTimeInterface $date): string
    {
        if ($date === null) {
            return 'Date inconnue';
        }

        $now  = new \DateTime();
        $diff = $now->diff($date);

        if ($diff->y > 0) {
            return 'il y a ' . $diff->y . ' an' . ($diff->y > 1 ? 's' : '');
        }
        if ($diff->m > 0) {
            return 'il y a ' . $diff->m . ' mois';
        }
        if ($diff->d > 6) {
            $weeks = (int) floor($diff->d / 7);
            return 'il y a ' . $weeks . ' semaine' . ($weeks > 1 ? 's' : '');
        }
        if ($diff->d > 0) {
            return 'il y a ' . $diff->d . ' jour' . ($diff->d > 1 ? 's' : '');
        }
        if ($diff->h > 0) {
            return 'il y a ' . $diff->h . ' heure' . ($diff->h > 1 ? 's' : '');
        }
        if ($diff->i > 0) {
            return 'il y a ' . $diff->i . ' minute' . ($diff->i > 1 ? 's' : '');
        }

        return 'à l\'instant';
    }

    // ─── Filtre cooking_time_format ───────────────────────────────────────────

    public function cookingTimeFormat(?int $minutes): string
    {
        if ($minutes === null || $minutes <= 0) {
            return '-';
        }

        $h   = intdiv($minutes, 60);
        $min = $minutes % 60;

        if ($h > 0 && $min > 0) {
            return $h . 'h' . str_pad((string)$min, 2, '0', STR_PAD_LEFT);
        }
        if ($h > 0) {
            return $h . 'h';
        }

        return $min . 'min';
    }

    // ─── Fonction difficulty_stars ────────────────────────────────────────────

    public function difficultyStars(string $difficulte): string
    {
        return match ($difficulte) {
            'facile'   => '⭐',
            'moyen'    => '⭐⭐',
            'difficile'=> '⭐⭐⭐',
            default    => '',
        };
    }
}
