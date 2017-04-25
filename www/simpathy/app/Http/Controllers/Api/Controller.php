<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\ApiException;
use App\Http\Controllers\Controller as BaseController;

abstract class Controller extends BaseController
{

    public function unsupportedMethod()
    {
        throw new ApiException('This method is not allowed for this resource');
    }

}
