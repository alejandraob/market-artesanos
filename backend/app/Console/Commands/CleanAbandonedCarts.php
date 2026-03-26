<?php

namespace App\Console\Commands;

use App\Models\Cart;
use Illuminate\Console\Command;

class CleanAbandonedCarts extends Command
{
    protected $signature = 'carts:clean {--days=7 : Dias de inactividad para considerar abandonado}';
    protected $description = 'Elimina carritos de invitados abandonados (sin actividad por X dias)';

    public function handle()
    {
        $days = $this->option('days');
        $cutoff = now()->subDays($days);

        $carts = Cart::whereNull('user_id')
            ->where('updated_at', '<', $cutoff)
            ->get();

        $count = $carts->count();

        foreach ($carts as $cart) {
            $cart->items()->delete();
            $cart->delete();
        }

        $this->info("Se eliminaron {$count} carritos abandonados (mas de {$days} dias).");

        return Command::SUCCESS;
    }
}
