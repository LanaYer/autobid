<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CreateProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create-products';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        echo 1;
    }
}
