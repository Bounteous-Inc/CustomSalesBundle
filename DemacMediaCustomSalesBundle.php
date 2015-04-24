<?php

namespace DemacMedia\Bundle\CustomSalesBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class DemacMediaCustomSalesBundle extends Bundle
{
    public function getParent()
    {
        return 'OroCRMSalesBundle';
    }
}
