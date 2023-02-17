<?php

namespace Statistics\Calculator;

use SocialPost\Dto\SocialPostTo;
use Statistics\Dto\StatisticsTo;

/**
 * Class AveragePostsPerUserPerMonth
 *
 * @package Statistics\Calculator
 */
class AveragePostsPerUserPerMonth extends AbstractCalculator
{
    protected const UNITS = 'posts';

    /**
     * @var array
     */
    private array $totals = [];

    /**
     * @inheritDoc
     */
    protected function doAccumulate(SocialPostTo $postTo): void
    {
        $authorId = $postTo->getAuthorId();
        $month = $postTo->getDate()->format("F");

        $this->totals[$month] = $this->totals[$month] ?? [];
        $this->totals[$month][$authorId] = ($this->totals[$month][$authorId] ?? 0) + 1;
    }

    /**
     * @inheritDoc
     */
    protected function doCalculate(): StatisticsTo
    {
        $stats = new StatisticsTo();
        foreach ($this->totals as $month => $authors) {
            $countAuthors = 0;
            $countPosts = 0;
            foreach ($authors as $posts) {
                $countAuthors++;
                $countPosts += $posts;
            }

            $stats->addChild((new StatisticsTo())
                ->setName($this->parameters->getStatName())
                ->setSplitPeriod($month)
                ->setValue(round($countPosts / $countAuthors))
                ->setUnits(self::UNITS));
        }

        return $stats;
    }
}
