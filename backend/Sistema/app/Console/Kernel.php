<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Comandos Artisan registrados para a aplicação.
     *
     * @var array
     */
    protected $commands = [
        \App\Console\Commands\MarkAbsences::class,
    ];

    /**
     * Define o agendamento de comandos da aplicação.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Marca falta automática às 10h para o turno da MANHÃ
        $schedule->command('absences:mark MANHÃ')->dailyAt('10:00');

        // Marca falta automática às 14h para o turno da TARDE
        $schedule->command('absences:mark TARDE')->dailyAt('14:00');
    }
}
