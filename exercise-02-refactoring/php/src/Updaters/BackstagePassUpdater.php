<?php

declare(strict_types=1);

namespace GildedRose\Updaters;

use GildedRose\Item;
use GildedRose\ItemUpdater;

final class BackstagePassUpdater implements ItemUpdater
{
    public function update(Item $item): void
    {
        // Aumentar quality según días restantes
        if ($item->quality < 50) {
            $item->quality++;

            // +1 adicional si faltan 10 días o menos
            if ($item->sellIn < 11 && $item->quality < 50) {
                $item->quality++;
            }

            // +1 adicional si faltan 5 días o menos
            if ($item->sellIn < 6 && $item->quality < 50) {
                $item->quality++;
            }
        }

        // Decrementar sellIn
        $item->sellIn--;

        // Después del concierto, quality = 0
        if ($item->sellIn < 0) {
            $item->quality = 0;
        }
    }
}
