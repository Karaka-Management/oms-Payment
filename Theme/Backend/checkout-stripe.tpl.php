<?php declare(strict_types=1);

$item = $request->getData('item');
?>
<div class="container">
    <h1>Checkout</h1>
    <div class="item">
        <h2><?= $item->getL11n('name1')->content; ?></h2>
        <p>Price: <strong><?= $item->salesPrice->getCurrency(); ?></strong></p>

    </div>
</div>