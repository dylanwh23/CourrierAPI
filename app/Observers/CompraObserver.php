<?php

namespace App\Observers;

use App\Models\Compra;

class CompraObserver
{
    /**
     * Handle the Compra "created" event.
     */
    public function created(Compra $compra): void
    {
        //
        $orden = $compra->orden;
        if ($orden) {
            // Actualiza el valor total de la orden al crear una nueva compra
            $orden->actualizarValorTotal();
            $orden->actualizarStatus('En espera');
        }
    }

    /**
     * Handle the Compra "updated" event.
     */
    public function updated(Compra $compra): void
    {
        //
         if ($compra->estado === 'Arribado') {
            
            // Carga la relación 'orden' para acceder a ella
            $orden = $compra->orden;

            // Si no hay orden asociada, no hagas nada
            if (!$orden) {
                return;
            }

            // Cuentas el total de compras que NO están en estado "Arribado"
            $comprasNoArribadas = $orden->compras()->where('estado', '=', 'En viaje a casilla')->count();

            // Si el contador es 0, significa que todas las compras han arribado
            if ($comprasNoArribadas === 0) {
                // Cambias el estado de la orden y guardas
                $orden->status = 'Compras arribadas';
                $orden->save();
            }
        }
    }

    /**
     * Handle the Compra "deleted" event.
     */
    public function deleted(Compra $compra): void
    {
        //
    }

    /**
     * Handle the Compra "restored" event.
     */
    public function restored(Compra $compra): void
    {
        //
    }

    /**
     * Handle the Compra "force deleted" event.
     */
    public function forceDeleted(Compra $compra): void
    {
        //
    }
}
