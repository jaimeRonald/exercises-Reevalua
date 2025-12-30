<?php

declare(strict_types=1);

namespace GildedRose\Updaters;

use GildedRose\Item;
use GildedRose\ItemUpdater;

final class SulfurasUpdater implements ItemUpdater
{
    public function update(Item $item): void
    {
        // Sulfuras es legendario - nunca cambia
        // No hace nada
    }
}
