<?php

namespace Happypixels\Shopr\Tests\Feature\Cart;

use Happypixels\Shopr\Tests\TestCase;
use Happypixels\Shopr\Contracts\Cart;
use Happypixels\Shopr\Tests\Support\Models\TestShoppable;

class UpdateCartItemTest extends TestCase
{
    /** @test */
    public function it_updates_the_item_quantity()
    {
        $cart  = app(Cart::class);
        $model = TestShoppable::first();
        $item  = $cart->addItem(get_class($model), 1, 1);

        $this->assertEquals(1, $cart->items()->first()->quantity);

        $response = $this->json('PATCH', 'api/shopr/cart/items/' . $item->id, ['quantity' => 2])
            ->assertStatus(200)
            ->assertJsonFragment(['count' => 2]);

        $this->assertEquals(2, $cart->count());
        $this->assertEquals(2, $cart->items()->first()->quantity);
    }

    /** @test */
    public function it_updates_the_cart_totals_correctly()
    {
        $cart  = app(Cart::class);
        $model = TestShoppable::first();
        $item  = $cart->addItem(get_class($model), $model->id, 1, [], [
            ['shoppable_type' => get_class($model), 'shoppable_id' => 1],
            ['shoppable_type' => get_class($model), 'shoppable_id' => 1, 'options' => ['color' => 'Green']],
        ]);

        $response = $this->json('PATCH', 'api/shopr/cart/items/' . $item->id, ['quantity' => 2])
            ->assertStatus(200)
            ->assertJsonFragment(['count' => 2, 'total' => 3000]);

        // Make sure the quantity and totals of all subItems are updated as well.
        $subItems = $cart->items()->first()->subItems;
        $this->assertEquals([2, 2], $subItems->pluck('quantity')->toArray());
        $this->assertEquals([1000, 1000], $subItems->pluck('total')->toArray());
    }
}
