<?php

namespace ApipublicaBundle\Controller;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\IpUtils;

class HealthController extends Controller
{
    protected $ips = [
        '10.10.28.149',
        '10.10.28.150',
        '127.0.0.1',
    ];

    protected $ipRanges = [
        '10.40.0.0/14',
        '10.44.0.0/14',
    ];

    /**
     * Para acceder a este metodo, se require autorizacion(token).
     *
     * @ApiDoc(
     *     section = "Health",
     *     description="Devuelve el status de  un servicio especificado, con 200, en otro caso 500 ó 403",
     *     requirements={
     *     {"name"="param",        "dataType"="string",  "required"="true", "default"="ping | dbProd | dbLeg | elastic | all ",  "description"="ping->Ejecución de un Ping en la apipublica ;  dbProd->Regresa el estatus de la Base de Datos de Produccion | dbLeg->Regresa el estatus de la Base de Datos de Legacy | elastic->Regresa el estatus de Elastic Search | all->Todo lo anterior en un arreglo"}
     *    },
     * )
     */
    public function checkAction(Request $request, $param = 'all')
    {
        $helpers = $this->get('app.helpers');
        //$param = $request->get("param");

        // Protect for non Authorized IPs
        foreach ($request->getClientIps() as $ip) {
            if (!$this->isValidIp($ip) && !$this->isValidIpRange($ip)) {
                $msg = sprintf('Your IP: (%s), is not allowed to access this URL, BYE BYE!', $ip);

                $data = $helpers->responseData($code = 403, $msg);

                return $helpers->responseHeaders($code = 403, $data);
            }
        }

        $resp = $this->testLayers($param);

        if (isset($resp['error']) && $resp['error']) {
            return $helpers->responseHeaders($code = 500, $resp);
        }

        if (isset($resp['message'])) {
            $response = array(
                'error_code' => 0,
                'message' => $resp['message'],
            );
        } else {
            $response = array(
                'error_code' => 0,
                'message' => $resp,
            );
        }

        return $helpers->json($response, true);
    }

    protected function isValidIp($ip)
    {
        return in_array($ip, $this->container->getParameter('array_ips'));
    }

    protected function isValidIpRange($ip)
    {
        return IpUtils::checkIp($ip, $this->ipRanges);
    }

    protected function testLayers($layer = 'all')
    {
        $resp = true;

        switch ($layer) {
            case 'ping': $resp = $this->checkPing();
                break;
            case 'dbProd': $resp = $this->checkDoctrineProd();
                break;
            case 'dbLeg': $resp = $this->checkDoctrineLeg();
                break;
            case 'elastic': $resp = $this->checkElasticSearch();
                break;
            case 'all':
                $respC = $this->checkPing();
                $respD = $this->checkDoctrineProd();
                $respE = $this->checkDoctrineLeg();
                $respF = $this->checkElasticSearch();

                //var_dump($respD, $respR);

                $respTemp = array();
                if (is_array($respC)) {
                    $respTemp[] = $respC;
                }
                if (is_array($respD)) {
                    $respTemp[] = $respD;
                }
                if (is_array($respE)) {
                    $respTemp[] = $respE;
                }
                if (is_array($respF)) {
                    $respTemp[] = $respF;
                }

                $resp = (empty($respTemp)) ? true : $respTemp;

                break;
            default:
                return $this->buildError('Non-Existent', sprintf('Option not supported:'.$layer));
        }

        return $resp;
    }

    protected function checkPing()
    {
        return array(
            'error' => false,
            'message' => 'PONG',
        );
    }

    protected function checkDoctrineProd()
    {
        try {
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->connect();
            $connected = $em->getConnection()->isConnected();

            return array(
                'error' => !$connected,
                'message' => 'PONG DB PROD',
            );
        } catch (\Exception $e) {
            return $this->buildError('doctrine', sprintf('Doctrine Production doesn\'t feel fine : (%s)', $e->getMessage()));
        }
    }

    protected function checkDoctrineLeg()
    {
        try {
            $em = $this->getDoctrine()->getManager('efOld');
            $em->getConnection()->connect();
            $connected = $em->getConnection()->isConnected();

            return array(
                'error' => !$connected,
                'message' => 'PONG DB LEGACY',
            );
        } catch (\Exception $e) {
            return $this->buildError('doctrine', sprintf('Doctrine Legacy doesn\'t feel fine : (%s)', $e->getMessage()));
        }
    }

    protected function checkElasticSearch()
    {
        // app/config/misc/fos_elastica.yml
        // vendor/wfcms/cms-base-bundle/Wf/Bundle/CmsBaseBundle/Resources/config/wf_elastica.xml
        $client = $this->get('fos_elastica.client.default');

        if (!$client->hasConnection()) {
            return $this->buildError('elastic', sprintf('ElasticSearch doesn\'t feel fine (%s)', $client->hasConnection()));
        }

        return array(
            'error' => !$client->hasConnection(),
            'message' => 'PONG DB Elastic',
        );
    }

    protected function buildError($layer, $message, $code = 500)
    {
        return array(
            'error' => true,
            'source' => $layer,
            'message' => $message,
            'error_code' => $code,
        );
    }
}
