<?php

namespace DemacMedia\Bundle\CustomSalesBundle\Controller\Api\Rest;

use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SoapBundle\Controller\Api\Rest\RestController;

/**
 * @RouteResource("wufoo_credentials")
 * @namePrefix("demac_api_")
 */
class WufooCredentialsController extends RestController
{
    /**
     * @Acl(
     *      id="demac_wufoo_credentials_delete_acl",
     *      type="entity",
     *      class="DemacMediaCustomSalesBundle:WufooCredentials",
     *      permission="DELETE"
     * )
    */
    public function deleteAction($id)
    {
        return $this->handleDeleteRequest($id);
    }

    public function getForm()
    {
    }

    public function getFormHandler()
    {
    }

    public function getManager()
    {
        return $this->get('demacmedia.wufoo.credentials_manager.api');
    }
}