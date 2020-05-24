<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CreateCart extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create-cart';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $job = new \App\Jobs\CreateCart();
        $job->handle();
    }
}
