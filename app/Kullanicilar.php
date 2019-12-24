<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class Kullanicilar extends Eloquent
{
   protected $collection = 'kullanicilar';
}
