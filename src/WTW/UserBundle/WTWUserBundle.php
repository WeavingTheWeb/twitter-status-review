<?php

namespace WTW\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class WTWUserBundle extends Bundle
{
    public function getParent()
    {
        return 'FOSUserBundle';
    }
}
