<?php

$item = $request->getData('item');
?>
<div class="container">
    <h1>Checkout</h1>
    <div class="item">
        <h2><?= $item->getL11n('name1')->description; ?></h2>
        <p>Price: <strong><?= $item->salesPrice->getCurrency(); ?></strong></p>

    </div>
</div>