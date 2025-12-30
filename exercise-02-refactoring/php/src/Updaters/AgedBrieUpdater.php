<?php

declare(strict_types=1);

namespace GildedRose\Updaters;

use GildedRose\Item;
use GildedRose\ItemUpdater;

final class AgedBrieUpdater implements ItemUpdater
{
    public function update(Item $item): void
    {
        // Aumentar quality (máximo 50)
        if ($item->quality < 50) {
            $item->quality++;
        }

        // Decrementar sellIn
        $item->sellIn--;

        // Si pasó la fecha, aumentar quality otra vez
        if ($item->sellIn < 0 && $item->quality < 50) {
            $item->quality++;
        }
    }
}
