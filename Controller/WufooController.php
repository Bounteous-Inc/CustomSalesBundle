<?php

namespace DemacMedia\Bundle\CustomSalesBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpFoundation\Request;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\IntegrationBundle\Provider\Rest\Client\Guzzle\GuzzleRestClientFactory;
use DemacMedia\Bundle\CustomSalesBundle\Entity\WufooCredentials;
use Symfony\Component\HttpFoundation\JsonResponse;


use Oro\Bundle\IntegrationBundle\Provider\Rest\Client\Guzzle;


class WufooController extends Controller
{
    /**
     * @Route("/wufoo", name="demac_media_customsales_wufoo")
     * @Acl(
     *     id="demac_wufoo_credentials_view_acl",
     *     type="entity",
     *     class="DemacMediaCustomSalesBundle:WufooCredentials",
     *     permission="VIEW"
     * )
     */
    public function indexAction() {
        return $this->redirectToRoute('demac_wufoo_credentials');
    }


    /**
     * @Route("/wufoo/credentials", name="demac_wufoo_credentials")
     * @Template
     * @Acl(
     *     id="demac_wufoo_credentials_view_acl",
     *     type="entity",
     *     class="DemacMediaCustomSalesBundle:WufooCredentials",
     *     permission="VIEW;EDIT"
     * )
     */
    public function credentialsAction(Request $request) {
        return $this->render('DemacMediaCustomSalesBundle:WufooPages:wufooCredentials.html.twig');
    }



    /**
     * @Route("/wufoo/credentials/view/{id}", name="demac_wufoo_credentials_view", requirements={"id"="\d+"})
     * @Template("DemacMediaCustomSalesBundle:WufooPages:wufooView.html.twig")
     * @AclAncestor("demac_wufoo_credentials_view")
     */
    public function viewAction(WufooCredentials $wufooCredentials)
    {
        return array(
            'wufooCredentials' => $wufooCredentials,
            'entity' => $wufooCredentials
        );
    }



    /**
     * @Route("/wufoo/credentials/create", name="demac_wufoo_credentials_create")
     * @Template("DemacMediaCustomSalesBundle:WufooPages:wufooUpdate.html.twig")
     * @Acl(
     *     id="demac_wufoo_credentials_create_acl",
     *     type="entity",
     *     class="DemacMediaCustomSalesBundle:WufooCredentials",
     *     permission="CREATE"
     * )
     */
    public function createAction(Request $request)
    {
        return $this->update(new WufooCredentials(), $request);
    }


    /**
     * @Route("/wufoo/credentials/update/{id}", name="demac_wufoo_credentials_update", requirements={"id":"\d+"}, defaults={"id":0})
     * @Template("DemacMediaCustomSalesBundle:WufooPages:wufooUpdate.html.twig")
     * @Acl(
     *     id="demac_wufoo_credentials_update_acl",
     *     type="entity",
     *     class="DemacMediaCustomSalesBundle:WufooCredentials",
     *     permission="EDIT"
     * )
     */
    public function updateAction(WufooCredentials $wufooCredentials, Request $request)
    {
        return $this->update($wufooCredentials, $request);
    }


    private function update(WufooCredentials $wufooCredentials, Request $request)
    {
        $form = $this->get('form.factory')->create('wufoo_credentials', $wufooCredentials);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($wufooCredentials);
            $entityManager->flush();

            return $this->get('oro_ui.router')->redirectAfterSave(
                array(
                    'route' => 'demac_wufoo_credentials_update',
                    'parameters' => array('id' => $wufooCredentials->getId()),
                ),
                array('route' => 'demac_media_customsales_wufoo'),
                $wufooCredentials
            );
        }

        return array(
            'entity' => $wufooCredentials,
            'form' => $form->createView(),
        );
    }



    /**
     * @Route("/wufoo/get-data/{email}", name="demac_media_customsales_wufoo_get")
     * @Acl(
     *     id="demac_wufoo_credentials_view_acl",
     *     type="entity",
     *     class="DemacMediaCustomSalesBundle:WufooCredentials",
     *     permission="VIEW"
     * )
     *
     * @return JsonResponse
     */
    public function getDataAction($email) {

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \Exception('Invalid e-mail');
        }

        $wufooData = $this->getWufooData($email);
        return $wufooData;
    }


    protected function getWufooData($email) {
        $data = [];

        $wufooConfig = $this->loadWufooConfig();
        if (!$wufooConfig) {
            return false;
        }

        for ($x = 0; $x < sizeof($wufooConfig); $x++) {

            $emailField = [
                'virtual_preview_form' => 'Field2',
                'sample_request_form'  => 'Field9',
                'catalog_request_form' => 'Field4'
            ];

            $filter = [
                'Filter1' => urldecode($emailField[$wufooConfig[$x]->getFormName()]. '+Is_equal_to+' .$email),
                'sort' => 'EntryId',
                'sortDirection' => 'DESC',
            ];

            $wufoo = $this->setWufooAuth($wufooConfig);
            if (!$wufoo) {
                return false;
            }

            $formHash = $wufooConfig[$x]->getFormHash();

            $result = $wufoo->getJSON(
                '/api/v3/forms/' .$formHash. '/entries/count.json',
                [],
                [],
                ['query' => $filter]
            );

            if ($result['EntryCount'] > 0) {

                $formName = $wufooConfig[$x]->getFormName();

                $data[$formName][] = $wufoo->getJSON(
                    '/api/v3/forms/' .$formHash. '/entries.json',
                    [],
                    [],
                    ['query' => $filter]
                );
            }



            unset($wufoo);

        }
        // $return = $data;
        return new JsonResponse($data);
    }


    protected function setWufooAuth($wufooConfig) {
        try {

            $guzzleFactory = new GuzzleRestClientFactory();
            $options = [
                'auth' => [
                    $wufooConfig[0]->getApiKey(),
                    $wufooConfig[0]->getApiUser(),
                    'basic'
                ]
            ];
            $baseUrl = $wufooConfig[0]->getDomainName();
            $wufooClient = $guzzleFactory->createRestClient($baseUrl, $options);

        } catch( \Exception $e ) {
            return $e->getMessage();
        }
        return $wufooClient;
    }


    /**
     * @return array WufooCredentials
     */
    protected function loadWufooConfig() {
        try {
            $wufooConfig = $this
                ->getDoctrine()
                ->getRepository('DemacMediaCustomSalesBundle:WufooCredentials')
                ->findAll();

            if ($wufooConfig) {
                return $wufooConfig;
            }
        } catch( \Exception $e) {
            return $e->getMessage();
        }
    }
}
