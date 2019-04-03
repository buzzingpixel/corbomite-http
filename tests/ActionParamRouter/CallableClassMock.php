<?php

declare(strict_types=1);

namespace corbomite\tests\ActionParamRouter;

use Zend\Diactoros\Response;

class CallableClassMock
{
    public $response;

    public function callableMethod()
    {
        if (! $this->response) {
            $response       = new Response();
            $response       = $response->withStatus(598);
            $this->response = $response;
        }

        return $this->response;
    }
}
