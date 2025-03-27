<?php

/*
require 'kirby/bootstrap.php';

echo (new Kirby())->render();
*/

// require Kirby's bootstrap file
require __DIR__ . '/kirby/bootstrap.php';

// require the CustomKirby class
require __DIR__ . '/site/plugins/extend-core-classes/classes/CustomKirby.php';

// import class
use cookbook\core\CustomKirby;

echo (new CustomKirby())->render();
