<?php

declare(strict_types=1);

namespace GildedRose;

use GildedRose\Updaters\AgedBrieUpdater;
use GildedRose\Updaters\BackstagePassUpdater;
use GildedRose\Updaters\ConjuredItemUpdater;
use GildedRose\Updaters\NormalItemUpdater;
use GildedRose\Updaters\SulfurasUpdater;

final class ItemUpdaterFactory
{
    public function create(Item $item): ItemUpdater
    {
        return match (true) {
            str_contains($item->name, 'Sulfuras') => new SulfurasUpdater(),
            str_contains($item->name, 'Aged Brie') => new AgedBrieUpdater(),
            str_contains($item->name, 'Backstage passes') => new BackstagePassUpdater(),
            str_contains($item->name, 'Conjured') => new ConjuredItemUpdater(),
            default => new NormalItemUpdater(),
        };
    }
}
