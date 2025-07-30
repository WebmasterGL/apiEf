<?php

namespace BackendBundle\Controller;

use BackendBundle\Entity\Category;
use BackendBundle\Entity\Columna;
use BackendBundle\Entity\Flags;
use BackendBundle\Entity\Image;
use BackendBundle\Entity\Page;
use BackendBundle\Entity\Tag;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Constraints as Assert;
use BackendBundle\Entity\Personaje;
use BackendBundle\Entity\Author;
use BackendBundle\Entity\WfUser;

use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Security\Core\Role\RoleHierarchy;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Yaml;


class APIController extends Controller
{


    /**
     * @ApiDoc(
     *  section = "TestToken",
     *  description="Probar la validez de un token",
     *     requirements={
     *      {"name"="json", "dataType"="array", "required"=true, "description"="user, password, gethash[optional]"},
     *    },
     *     headers={
     *      {"name"="authorization", "dataType"="string", "required"=true, "description"="token authorization"}
     *    }
     * )
     */
    public function testTokenAction(Request $request)
    {


        $helpers = $this->get("app.helpers");

        $elhash = $request->headers->get('Authorization');

        $token = explode(" ", $elhash);




        $identity = $helpers->authCheck($token[1], true);

        $data = $helpers->responseData( 200, $identity);
        $response = $helpers->responseHeaders( 200, $data);

        return $response;


    }

    /**
     * @ApiDoc(
     *  section = "TestToken",
     *  description="Nueva Columna",
     *     requirements={
     *      {"name"="json", "dataType"="array", "required"=true, "description"="user, password, gethash[optional]"},
     *    },
     *     headers={
     *      {"name"="authorization", "dataType"="string", "required"=true, "description"="token authorization"}
     *    }
     * )
     */
    public function newColumnAction(){

        $helpers = $this->get("app.helpers");

        $em= $this->getDoctrine()->getEntityManager();

        $columna_repo = $em->getRepository("BackendBundle:Columna");

        $columna = $columna_repo->find(1);

        $autores = $columna->getAuthor();

        $data = $helpers->responseData( 200, $autores);
        $response = $helpers->responseHeaders( 200, $data);

        return $response;

    }
    /**
     * @ApiDoc(
     *  section = "TestToken",
     *  description="Probar la validacion de Flags",
     *     requirements={
     *
     *    },
     *     headers={
     *      {"name"="authorization", "dataType"="string", "required"=true, "description"="token authorization"}
     *    }
     * )
     */
    public function testValidationAction(Request $request)
    {

        $helpers = $this->get("app.helpers");


        $entidad = new WfUser();

        $email = "";

        $entidad->setEmail($email);




        $validator = $this->get('validator');

        $errors = $validator->validate($entidad);


        foreach ($errors as $error) {
            $messages[] = $error->getMessage();
        }


        if(count($errors)>0){
            $data = $helpers->responseData( 400, $messages);
            $response = $helpers->responseHeaders( 400, $data);
        }else{
            $data = $helpers->responseData( 200, "success");
            $response = $helpers->responseHeaders( 200, $data);
        }




        return $response;

    }



    public function createNewAction(Request $request)
    {
        $helpers = $this->get("app.helpers");

        $hash = $request->get("authorization", null);


        $authCheck = $helpers->authCheck($hash, false);


        if ($authCheck) {

            $identity = $helpers->authCheck($hash, true);
            $json = $request->get("json", null);
            if ($json != null) {


                $data = array("status" => "error",
                    "code" => 200,
                    "msg" => "Ejecucion exitosa",
                    "name" => $identity->email
                );

            } else {
                $data = array("status" => "error",
                    "code" => 400,
                    "msg" => "Missing json"
                );
            }

        } else if ($authCheck < 0) {
            $data = array(
                "status" => "error",
                "code" => 300,
                "msg" => "Token Expired"
            );
        } else {
            $data = array("status" => "error",
                "code" => 400,
                "msg" => "Authentication new failed"
            );
        }

        return $helpers->json($data);
    }

    public function newPersonajeAction(Request $request)
    {
        $helpers = $this->get("app.helpers");
        $hash = $request->get("authorization", null);

        $authCheck = $helpers->authCheck($hash);

        if ($authCheck == true) {

            $identity = $helpers->authCheck($hash, true);
            $json = $request->get("json", null);

            if ($json != null) {
                $params = json_decode($json);

                $user_id = ($identity->sub != null) ? $identity->sub : null;
                $nombre = (isset($params->nombre)) ? $params->nombre : null;
                $titulo = (isset($params->titulo)) ? $params->titulo : null;
                $puesto = (isset($params->puesto)) ? $params->puesto : null;
                $foto = (isset($params->foto)) ? $params->foto : null;

                if ($user_id != null && $nombre != null) {

                    $em = $this->getDoctrine()->getManager();

                    $personaje = new Personaje();

                    $personaje->setNombre($nombre);
                    $personaje->setTitulo($titulo);
                    $personaje->setPuesto($puesto);
                    $personaje->setFoto($foto);
                    $em->persist($personaje);
                    $em->flush();

                    //Hago una consulta a la entidad Personaje, con los datos recibidos via Json
                    $personaje = $em->getRepository("BackendBundle:Personaje")->findOneBy(array(
                        "nombre" => $nombre,
                        "titulo" => $titulo,
                        "puesto" => $puesto,
                        "foto" => $foto
                    ));

                    $data = array(
                        "status" => "success personaje creado",
                        "code" => 200,
                        "data" => $personaje
                    );

                } else {
                    $data = array(
                        "status" => "error",
                        "code" => 400,
                        "msg" => "Personaje not created"
                    );
                }
            } else {
                $data = array(
                    "status" => "error",
                    "code" => 400,
                    "msg" => "Personaje not created, params failed"
                );
            }
        } else if ($authCheck < 0) {
            $data = array(
                "status" => "error",
                "code" => 300,
                "msg" => "Token Expired"
            );
        } else {
            $data = array(
                "status" => "error",
                "code" => 400,
                "msg" => "Authorization not valid"
            );
        }

        return $helpers->json($data);

    }


    public function editPersonajeAction(Request $request, $id)
    {
        $helpers = $this->get("app.helpers");
        $hash = $request->get("authorization", null);

        $authCheck = $helpers->authCheck($hash);

        if ($authCheck == true) {
            $identity = $helpers->authCheck($hash, true);
            $json = $request->get("json", null);

            if ($json != null) {
                $params = json_decode($json);

                $user_id = ($identity->sub != null) ? $identity->sub : null;
                $nombre = (isset($params->nombre)) ? $params->nombre : null;
                $titulo = (isset($params->titulo)) ? $params->titulo : null;
                $puesto = (isset($params->puesto)) ? $params->puesto : null;
                $foto = (isset($params->foto)) ? $params->foto : null;

                if ($user_id != null && $nombre != null) {
                    //Busco en la BD el personaje que vamos a editar, a traves del video_id que agarro de la url
                    $em = $this->getDoctrine()->getManager();

                    $personaje = $em->getRepository("BackendBundle:Personaje")->findOneBy(array(
                        "id" => $id
                    ));

                    $personaje->setNombre($nombre);
                    $personaje->setTitulo($titulo);
                    $personaje->setPuesto($puesto);
                    $personaje->setFoto($foto);
                    $em->persist($personaje);
                    $em->flush();

                    $data = array(
                        "status" => "success",
                        "code" => 200,
                        "msg" => "Personaje updated success!!"
                    );
                } else {
                    $data = array(
                        "status" => "error",
                        "code" => 400,
                        "msg" => "Personaje updated error"
                    );
                }

            } else {
                $data = array(
                    "status" => "error",
                    "code" => 400,
                    "msg" => "Personaje not updated, params failed"
                );
            }

        } else if ($authCheck < 0) {
            $data = array(
                "status" => "error",
                "code" => 300,
                "msg" => "Token Expired"
            );
        } else {
            $data = array(
                "status" => "error",
                "code" => 400,
                "msg" => "Authorization not valid"
            );
        }
        return $helpers->json($data);

    }


    /**
     * @ApiDoc(
     *  section = "TestToken",
     *  description="Limpieza de Portadas y Folds",
     *     requirements={
     *     {"name"="source", "dataType"="integer", "required"=true, "description"="Desde donde se ejecuta 1=endpoint, 2=command"},
     *      {"name"="dias", "dataType"="integer", "required"=true, "description"="Dias a respetar en la BD"},
     *     {"name"="task", "dataType"="integer", "required"=true, "description"="0:Imprime cuantas Portadas, 1:Imprime cuantos Folds, 2:Borra Portadas y Folds"},
     *     {"name"="em", "dataType"="string", "required"=false, "description"="Manejador de em (solo el command lo usa internamente)"},
     *     {"name"="helpers", "dataType"="string", "required"=false, "description"="Manejador de Helpers (solo el command lo usa internamente)"},
     *     {"name"="mindiasvigentes", "dataType"="integer", "required"=false, "description"="Minimo numero de dias vigentes (solo el command lo usa internamente)"}
     *    },
     *     headers={
     *      {"name"="authorization", "dataType"="string", "required"=true, "description"="token authorization"}
     *    }
     * )
     */
    public function cleanFoldsAction($source, $dias , $task, $em,$helpers, $mindiasvigentes) //Request $request)
    {

        if ($source == 1){ //llamada desde endpoint

            $em = $this->getDoctrine()->getManager();
            $helpers = $this->get("app.helpers");
            $mindiasvigentes = $this->container->getParameter('minDiasVigentesPortadas');

        }



        if($dias<$mindiasvigentes){

            $msg="Los días a respetar deben ser mayores a los especificados en parameters.yml(minDiasVigentesPortadas):".$mindiasvigentes;
            $data = $helpers->responseData($code = 200, $msg );
            $response = $helpers->responseHeaders($code = 200, $data);

            return $response;
        }

        if(empty($dias))
            $l_dias=$mindiasvigentes;
        else
            $l_dias = $dias;



        $query = $em->getRepository("BackendBundle:Portada")->createQueryBuilder('p')//tambien se tarda
        ->where("DATE_DIFF(now(), p.createdAt )>:dias")
            ->andWhere('p.status != :p_status')
            ->setParameter('dias', $l_dias)
            ->setParameter('p_status', 'published')
            ->getQuery();

        $portadas = $query->getResult();




        $msg = "";


        switch($task)
        {
            case 0:

                foreach ($portadas as $portada){
                    //echo ($portada . ": Folds:{" . count($portada->getMisFolds()) . "}" );
                }
                $msg = count($portadas)." Portadas a borrar";
                break;
            case 1:
                $totalFolds=0;
                foreach ($portadas as $portada){

                    $portadafolds = $em->getRepository('BackendBundle:PortadaFolds')->findBy(array('idportada' => $portada->getId()));
                    $totalFolds+=count($portadafolds);

                }
                $msg = $totalFolds." Folds a borrar";
                break;
            case 2:

                /*Borrando las Portadas DB y ES, Folds DB*/
                $totalFolds=0;
                foreach ($portadas as $portada){

                    $portadafolds = $em->getRepository('BackendBundle:PortadaFolds')->findBy(array('idportada' => $portada->getId()));

                    foreach ($portadafolds as $mypFold){
                        $totalFolds++;
                        $em->remove($mypFold);

                    }

                    $em->remove($portada);


                }
                $em->flush();

                $msg = count($portadas)." Portadas borradas . ". $totalFolds ." folds borrados";
                break;
        }



        $data = $helpers->responseData($code = 200, $msg );
        $response = $helpers->responseHeaders($code = 200, $data);

        return $response;


    }

}
