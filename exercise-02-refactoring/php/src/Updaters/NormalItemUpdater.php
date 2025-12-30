<?php

declare(strict_types=1);

namespace GildedRose\Updaters;

use GildedRose\Item;
use GildedRose\ItemUpdater;

final class NormalItemUpdater implements ItemUpdater
{
    public function update(Item $item): void
    {
        // Decrementar quality
        if ($item->quality > 0) {
            $item->quality--;
        }

        // Decrementar sellIn
        $item->sellIn--;

        // Si pasó la fecha, decrementar quality otra vez
        if ($item->sellIn < 0 && $item->quality > 0) {
            $item->quality--;
        }
    }
}
