<?php

declare(strict_types=1);

namespace GildedRose\Updaters;

use GildedRose\Item;
use GildedRose\ItemUpdater;

final class ConjuredItemUpdater implements ItemUpdater
{
    public function update(Item $item): void
    {
        // Decrementar quality el DOBLE de rápido
        if ($item->quality > 1) {
            $item->quality -= 2;
        } elseif ($item->quality > 0) {
            $item->quality = 0;
        }

        // Decrementar sellIn
        $item->sellIn--;

        // Si pasó la fecha, decrementar quality el doble otra vez (4 total)
        if ($item->sellIn < 0) {
            if ($item->quality > 1) {
                $item->quality -= 2;
            } elseif ($item->quality > 0) {
                $item->quality = 0;
            }
        }
    }
}
