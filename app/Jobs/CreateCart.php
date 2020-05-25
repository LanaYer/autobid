<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Product;
use App\Models\Cart;
use App\Models\Iteration;
use Illuminate\Support\Facades\DB;

class CreateCart implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // Минимальное количество продуктов в корзине
    protected $countFrom = 2500;

    // Максимальное количество продуктов в корзине
    protected $countTo = 3000;

    // Минимальная общая стоимость
    protected $minSumPrice = 2600000;

    // Максимальная общая стоимость
    protected $maxSumPrice = 3000000;

    // Шаг для количества (чем меньше, тем точнее)
    protected $countStep = 1;

    // Шаг для цены (чем меньше, тем точнее)
    protected $priceStep = 100;

    // Точность для цены (возьмем максимальную цену товара в корзине)
    protected $pricePrecision = 10000;

    /**
     * @param $limit
     * @param $offset
     * @return \Illuminate\Database\Query\Builder
     */
    public function makeQuery ($limit, $offset, $maxPrice) {
        $selectedQuery = DB::table(
        // Подзапрос для получения нужного количества товаров
            DB::raw("(select * from `products` where `price` <= "
                . $maxPrice . " order by `price` desc limit " . $limit .  " offset " . $offset .  ") as sub_query"));

        return $selectedQuery;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $limit = $this->countTo;
        $offset = 0;

        //чтобы набрать нужное количество тоаров, берем не самые дорогие
        $maxPrice = 7000;

        // Номер итерации
        $i = 1;

        // Сортируем все товары в порядке убывания цены
        do {
            // Общая сумма товаров из выборки
            $selectedPrice = self::makeQuery($limit, $offset, $maxPrice)->sum('price');
            $selectedCount = self::makeQuery($limit, $offset, $maxPrice)->count();

            //Проверяем, что суммарная стоимость товаров с максимальной ценой не ниже минимальной требуемой
            if ($selectedPrice < $this->minSumPrice) {
                echo "Невозможно сгенерировать корзину товаров, которая бы удовлетворяла заданным уловиям \n";
                //die;
            }

            //Сравнимаем разницу требуемой суммы и общей суммы выборки с заданной точностью
            if ((($selectedPrice - $this->maxSumPrice) > $this->pricePrecision) && $selectedCount > $this->countFrom) {
                $offset = $offset + $this->priceStep;
            } else if ($selectedPrice > $this->maxSumPrice) {
                //если нужная точность достигнута, начинаем убирать товары с минимальной стоимостью из корзины,
                // чтобы достичь необходимой суммы заказа
                $limit = $limit - $this->countStep;
            }

            $this->priceStep = $this->priceStep > 1 ? $this->priceStep - 1 : 1;
            $i++;
        //Выполняем до тех пор, пока сумма выборки больше максимальной требуемой
        } while ($selectedPrice > $this->maxSumPrice && $selectedCount > $this->countFrom);

        // Выводим результат итерации
        echo "Количество итераций - " . ($i - 1) . "\n" .
            "Количество товаров - " . $selectedCount . "\n" .
            "Общая стоимость - " . $selectedPrice . "\n\n" .
            "Запись в таблицу . . .";

        // Перед обновлением данных чищаем таблицу корзина
        Cart::query()->truncate();

        $cartItems = self::makeQuery($limit, $offset, $maxPrice)->pluck('id');

        // Задаем случайный id пользователя. Если бы была таблица пользователей, id был бы реальный
        $user_id = rand(1, 5);

        foreach ($cartItems as $item) {
            $cartItem = new Cart();
            $cartItem->user_id = $user_id;
            $cartItem->product_id = $item;
            $cartItem->save();
        }

        echo "Корзина создана";
    }
}
