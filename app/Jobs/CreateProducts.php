<?php

namespace App\Jobs;

use Faker\Generator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Faker\Provider\Barcode;
use App\Models\Product;

class CreateProducts implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // Количество продуктов в таблице
    protected $productsCount = 10000;

    //Начальный предел цены
    protected $priceFrom = 1;

    //Конечный предел цены
    protected $priceTo = 10000;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Генерируем случайный штрихкод продукта
        $barcode = new Barcode(new Generator());

        $i = 0;

        //Время перед началом выполнения скрипта
        $start = microtime(true);

        // Перед обновлением данных чищаем таблицу продуктов
        Product::query()->truncate();

        // В цикле заполняем таблицу продуктами со случайным штрихкодом и ценой
        while ($i < $this->productsCount) {
            $product = new Product();
            $product->barcode = $barcode->ean13();
            $product->price = rand($this->priceFrom, $this->priceTo);
            $product->save();
            $i++;
        }

        echo "Добавлено " . Product::query()->count() . " записей. Время выполнения скрипта "
            . round(microtime(true) - $start, 2) . " сек.";
    }
}
