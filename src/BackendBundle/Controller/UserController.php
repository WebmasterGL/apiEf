<?php

namespace BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\JsonResponse;
use BackendBundle\Entity\WfUser;

use Gedmo\Sluggable\Util\Urlizer;
use Symfony\Component\Validator\Constraints\DateTime;

class UserController extends Controller
{
    public $prefix_section = "ROLE_SECCION_";

    /**
     * @ApiDoc(
     *  section = "Login",
     *  description="Returns a token or data user, public method",
     *  requirements={
     *    {"name"="user", "dataType"="string", "required"=true, "description"="username"},
     *    {"name"="password", "dataType"="string", "required"=true, "description"="password"},
     *    {"name"="gethash", "dataType"="boolean", "required"=false, "description"="gethash"}
     *  }
     * )
     */
    public function loginAction(Request $request)
    {
        $helpers = $this->get("app.helpers");
        $jwt_auth = $this->get("app.jwt_auth");
        $user = $request->get("user", null);
        $password = $request->get("password", null);
        $getHash = $request->get("gethash", null);

        if ($user != null && $password != null) {
            if ($getHash == null || $getHash == "false") {
                //$signup = $jwt_auth->signup($email,$password, "hash"); //datos hasheados
                $signup = $jwt_auth->signup($user, $password); //datos limpios
            } else {
                //$signup = $jwt_auth->signup($email,$password, "hash"); //datos hasheados
                $signup = $jwt_auth->signup($user, $password, true); //datos hasheados
            }
            if ($signup == false) {
                $msg = 'Login failed, Credentials not valid';
                $response = $helpers->responseHeaders(403, $msg);
                return $response;
            }
            $helpers->logActivity($user, 'login');

            return new JsonResponse($signup);
        } else {
            $msg = 'Login not valid.';
            $data = $helpers->responseData(400, $msg);
            $response = $helpers->responseHeaders(400, $data);

            return $response;
        }
    }

    /**
     * @ApiDoc(
     *  section = "User",
     *  description="Regresa el listado de usuarios, private method",
     *   requirements={
     *      {"name"="page", "dataType"="int", "required"="false", "default"="1", "description"="numero de pagina, si se omite es 1"},
     *      {"name"="size", "dataType"="int", "required"="true", "default"="10", "description"="numero de items, si se omite es 10"}
     *   },
     *   headers={
     *      {"name"="Authorization", "dataType"="string", "required"=true, "description"="token authorization"}
     *    }
     * )
     * )
     */
    public function usersAction(Request $request, $iduser)
    {
        $helpers = $this->get("app.helpers");
        $em = $this->getDoctrine()->getManager();

        if ($iduser == NULL || $iduser == '{iduser}') //2o caso para aceptar el parametro que le pone swagger
        {
            $page = $request->get("page", 1);
            $size = $request->get("size", 10);

            $users = $em->getRepository("BackendBundle:WfUser")->findAll();

            $paginator = $this->get("knp_paginator");
            $items_per_page = $size;

            $pagination = $paginator->paginate($users, $page, $items_per_page);
            $total_items_count = $pagination->getTotalItemCount();

            $list = true;
            $data = $helpers->responseData(200, $msg = null, $list, $pagination, $total_items_count, $page, $items_per_page);

        } else {

            $user = $em->getRepository('BackendBundle:WfUser')->find($iduser);

            if (count($user) != 0) {

                $data = array(
                    "status" => "success",
                    "data" => $user
                );
            } else {
                $msg = "User not found";
                $data = $helpers->responseData($code = 404, $msg);
                $response = $helpers->responseHeaders($code = 404, $data);

                return $response;
            }
        }


        return $helpers->json($data);

    }

    /**
     * Descripcion especifica de este metodo
     *
     * @ApiDoc(
     *  section = "User",
     *  description="Crear nuevo usuario",
     *     requirements={
     *      {"name"="name", "dataType"="string", "required"=true, "description"="Name, is required"},
     *      {"name"="aPaterno", "dataType"="string", "required"=true, "description"="Apellido Paterno, is required"},
     *      {"name"="aMaterno", "dataType"="string", "required"=true, "description"="Apellido Materno, is required"},
     *      {"name"="email", "dataType"="string", "required"=true, "description"="Email, is required"},
     *      {"name"="username", "dataType"="string", "required"=true, "description"="UserName, is required"},
     *      {"name"="password", "dataType"="string", "required"=true, "description"="Password, is required"},
     *      {"name"="enabled", "dataType"="boolean", "required"=true, "description"="Enabled, is required"},
     *      {"name"="profileUser", "dataType"="string", "required"=true, "description"="Profile User, is required"},
     *      {"name"="sections", "dataType"="json", "required"=true, "description"="Sections,is required. Json example: [{'name':'ROLE_SECCION_EMPRESAS'}] "},
     *      {"name"="aditionalProfiles", "dataType"="json", "required"=true, "description"="Aditional Profiles, json example: [{'name':'ROLE_PAGE_TRASH'}]"}
     *    },
     *     headers={
     *      {"name"="Authorization", "dataType"="string", "required"=true, "description"="token authorization"}
     *    }
     * )
     */
    public function newUserAction(Request $request)
    {
        $helpers = $this->get("app.helpers");
        $em = $this->getDoctrine()->getManager();

        $name = $request->get('name');
        $aPaterno = $request->get('aPaterno');
        $aMaterno = $request->get('aMaterno');
        $email = $request->get('email');
        $username = $request->get('username');
        $password = $request->get('password');
        $enabled = $request->get('enabled');
        $profileUser = $request->get('profileUser');
        $sections = $request->get('sections');
        $aditionalProfiles = $request->get('aditionalProfiles');
        $msg = "User not created. No data";
        $validator = $this->get('validator');

        $user = new WfUser();
        $user->setUsername($username);
        $user->setUsernameCanonical($username);
        $user->setEmail($email);
        $user->setEmailCanonical($email);
        $user->setCredentialsExpireAt(new \DateTime());
        if ($enabled == 'true') {
            $user->setEnabled(1);
        } else {
            $user->setEnabled(0);
        }
        $user->setCredentialsExpired(0);
        //Genero Salt con el siguiente algoritmo
        $user->setSalt(base_convert(sha1(uniqid(mt_rand(), true)), 16, 36));
        //Cifrar el password
        //Mando a llamara mi servicio para encriptar la contraseña
        $jwt = $this->get("app.jwt_auth");
        $pwd = $jwt->encodePwd($user, $password, $user->getSalt());
        $user->setPassword($pwd);
        $array_roles = array(
            $profileUser
        );
        $sections_json = json_decode($sections);
        foreach ($sections_json as $mydata) {
            array_push($array_roles, $mydata->name);
        }
        if ($aditionalProfiles) {
            $aditionalProfiles_json = json_decode($aditionalProfiles);
            foreach ($aditionalProfiles_json as $mydata) {
                array_push($array_roles, $mydata->name);
            }
        }
        $user->setRoles($array_roles);
        $user->setFirstName($name);
        if ($aPaterno !== null) {
            $user->setAPaterno($aPaterno);
        }
        if ($aMaterno !== null) {
            $user->setAMaterno($aMaterno);
        }
        /*$array_slug = array(
            'name' => $name,
            'apellido_paterno' => $aPaterno,
            'apellido_materno' => $aMaterno
        );*/
        //Invoco mi funcion 'createSlug'
        /*$slug_final = $this->createSlug($array_slug);
        $user->setSlug($slug_final['value']);*/
        //Aqui compruebo que no exista el email en la BD, a la hora de registrarse
        $isset_user = $em->getRepository("BackendBundle:WfUser")->findBy(
            array(
                "email" => $email
            )
        );
        $isset_username = $em->getRepository("BackendBundle:WfUser")->findBy(
            array(
                "username" => $username
            )
        );
        //Si no existe un usuario con un email registrado, hacemos el registro de un nuevo usuario
        if (count($isset_user) == 0 && count($isset_username) == 0) {
            $errors = $validator->validate($user);
            foreach ($errors as $error) {
                $messages[] = $error->getMessage();
            }
            if (count($errors) > 0) {
                $data = $helpers->responseData(400, $messages);
                $response = $helpers->responseHeaders(400, $data);
            } else {
                $em->persist($user);
                $em->flush();
                $data = $helpers->responseData(200, "success");
                $response = $helpers->responseHeaders(200, $data);
            }
        } else {
            $msg = "User not created, because is duplicated. Email or username exist in DB";
            $data = $helpers->responseData(400, $msg);
            $response = $helpers->responseHeaders(400, $data);
        }

        return $response;
    }

    /**
     * Descripcion especifica de este metdo
     *
     * @ApiDoc(
     *     section = "User",
     *     description="Edita Usuario, metodo privado",
     *     requirements={
     *      {"name"="id", "dataType"="string", "required"=true, "description"="id user"},
     *      {"name"="name", "dataType"="string", "required"=true, "description"="Name"},
     *      {"name"="aPaterno", "dataType"="string", "required"=true, "description"="Apellido Paterno"},
     *      {"name"="aMaterno", "dataType"="string", "required"=true, "description"="Apellido Materno"},
     *      {"name"="email", "dataType"="string", "required"=true, "description"="Email"},
     *      {"name"="password", "dataType"="string", "required"=true, "description"="Password"},
     *      {"name"="enabled", "dataType"="boolean", "required"=true, "description"="Enabled"},
     *      {"name"="profileUser", "dataType"="string", "required"=true, "description"="Profile User"},
     *      {"name"="sections", "dataType"="json", "required"=true, "description"="Sections, json example: [{'name':'ROLE_SECCION_EMPRESAS'}] "},
     *      {"name"="aditionalProfiles", "dataType"="json", "required"=true, "description"="Aditional Profiles, json example: [{'name':'ROLE_PAGE_TRASH'}]"}
     *    },
     *    headers={
     *      {"name"="Authorization", "dataType"="string", "required"=true, "description"="token authorization"}
     *    }
     * )
     *
     */
    public function editUserAction(Request $request, $id = null)
    {
        $helpers = $this->get("app.helpers");
        $em = $this->getDoctrine()->getManager();

        $name = $request->get('name');
        $aPaterno = $request->get('aPaterno');
        $aMaterno = $request->get('aMaterno');
        $email = $request->get('email');
        $password = $request->get('password');
        $enabled = $request->get('enabled');
        $profileUser = $request->get('profileUser');
        $sections = $request->get('sections');
        $aditionalProfiles = $request->get('aditionalProfiles');

        $user_repo = $em->getRepository('BackendBundle:WfUser');
        $user_exist = $user_repo->find($id);

        if ($user_exist != null) {

            //Busco en la BD el usuario que vamos a editar, a traves del user_id que agarro de la url
            $user = $em->getRepository("BackendBundle:WfUser")->findOneBy(array(
                    "id" => $id
                )
            );
            if ($email != null) {
                $user->setEmail($email);
                $user->setEmailCanonical($email);
            }
            //Cifrar el password
            //Mando a llamara mi servicio para encriptar la contraseña
            if ($password != null) {
                //Genero Salt con el siguiente algoritmo
                $user->setSalt(base_convert(sha1(uniqid(mt_rand(), true)), 16, 36));
                $jwt = $this->get("app.jwt_auth");
                $pwd = $jwt->encodePwd($user, $password, $user->getSalt());
                $user->setPassword($pwd);
            }
            if (!empty($enabled)) {
                if ($enabled == 'true') {
                    $user->setEnabled(1);
                } else {
                    $user->setEnabled(0);
                }
            }
            if ($name != null) {
                $user->setFirstName($name);
            }
            if ($aPaterno != null) {
                $user->setAPaterno($aPaterno);
            }
            if ($aMaterno != null) {
                $user->setAMaterno($aMaterno);
            }

            $array_roles = array(
                $profileUser
            );
            if ($sections) {
                $sections_json = json_decode($sections);
                foreach ($sections_json as $mydata) {
                    array_push($array_roles, $mydata->name);
                }
            }
            if ($aditionalProfiles) {
                $aditionalProfiles_json = json_decode($aditionalProfiles);
                foreach ($aditionalProfiles_json as $mydata) {
                    array_push($array_roles, $mydata->name);
                }
            }
            $user->setRoles($array_roles);

            $em->persist($user);
            $em->flush();
            $data = $helpers->responseData(200, "success");
            $response = $helpers->responseHeaders(200, $data);

        } else {
            $msg = "User not found, in DB";
            $data = $helpers->responseData($code = 404, $msg);
            $response = $helpers->responseHeaders($code = 404, $data);
        }

        return $response;

    }

    /**
     * Delete User
     *
     * @ApiDoc(
     *     section = "User",
     *     description="Delete user",
     *     requirements={
     *      {"name"="id", "dataType"="string", "required"=true, "description"="id user"}
     *    },
     *    headers={
     *      {"name"="Authorization", "dataType"="string", "required"=true, "description"="token authorization"}
     *    }
     * )
     */
    public function deleteUserAction(Request $request, $id = null)
    {
        $helpers = $this->get("app.helpers");
        $em = $this->getDoctrine()->getManager();

        $user = $em->getRepository('BackendBundle:WfUser')->find($id);

        if ($user != null) {
            $em->remove($user);
            $em->flush();

            $msg = 'delete user success';
            $data = $helpers->responseData(200, $msg);
            $response = $helpers->responseHeaders(200, $data);
        } else {
            $msg = 'cannot delete user not found in DB';
            $data = $helpers->responseData($code = 404, $msg);
            $response = $helpers->responseHeaders($code = 404, $data);

        }

        return $response;

    }

    /**
     * @ApiDoc(
     *  section = "Logout",
     *  description="Logout a user, private method"
     * )
     */
    public function logoutAction()
    {
        $helpers = $this->get("app.helpers");
        $msg = 'Logout';
        $data = $helpers->responseData( 200, $msg);
        $response = $helpers->responseHeaders( 200, $data);
        return $response;
    }

    /**
     * @ApiDoc(
     *  section = "home",
     *  description="home despues del logout"
     * )
     */
    public
    function homeAction()
    {
        $helpers = $this->get("app.helpers");
        $data = array(
            "status" => "success",
            "code" => 200,
            "msg" => "en HOME"
        );
        return $helpers->json($data);
    }

    /**
     * @ApiDoc(
     *     section = "User",
     *     description = "Retrieve all security permissions",
     *     headers = {
     *       {"name" = "Authorization", "dataType"="string", "required"=true, "description"="token authorization"}
     *     }
     * )
     */
    public function getAllRolesAction()
    {
        $helpers = $this->get("app.helpers");
        $app_users = $this->get("app.users");
        $result = array();

        $all_roles = $app_users->getAllRolls();                                                 //Get roles
        foreach ($all_roles['ROLE_SUPER_ADMIN'] as $all_role) {
            $result['roles'][] = array(
                "key" => $all_role,
                "description" => $this->get('translator')->trans($all_role)
            );
        }
        $em = $this->getDoctrine()->getManager();

        $query = $em->createQuery('SELECT 
                                      cat,
                                      (SELECT c.title FROM BackendBundle:Category c WHERE c.id=cat.parentId ) parent
                                    FROM BackendBundle:Category cat'
                                 );
        $a = $query->getResult();

        foreach ($a as $val) {
            if ( $val[0]->getParentId() > 1 ){
                $desc = $val["parent"] . "-" . $val[0]->getTitle();
            }else{
                $desc = $val[0]->getTitle();
            }
            $result['sections'][] = array(
                "key" => $this->prefix_section . strtoupper(
                        str_replace("-", "_", $val[0]->getSlug() )
                    ),
                "description" => $desc,
            );
        }

        return $helpers->json($result);

    }

    /**
     * @ApiDoc(
     *  section = "User",
     *  description="Get user permissions (path,role,desc, IfIsCustomized) of current user",
     * )
     */
    public
    function getRollsAction(Request $request)
    {
        $helpers = $this->get("app.helpers");
        $result = array();

        $result = $this->getRoleArray(0);   //get all roles
        $data = array(
            "status" => "success",
            "code" => 200,
            "msg" => "User rolls",
            "result" => $result

        );

        return $helpers->json($data);
    }

    private
    function slugValidate($slug)
    {
        $em = $this->getDoctrine()->getManager();
        $page = $em->getRepository('BackendBundle:WfUser')->findOneBySlug($slug);
        return (($page == NULL) ? FALSE : TRUE);
    }

    private
    function createSlug($array_slug)
    {
        if ($array_slug['name'] != NULL && $array_slug['apellido_paterno'] != NULL && $array_slug['apellido_materno'] != NULL) {
            //$category = $em->getRepository('BackendBundle:Category')->find($array_slug['category']);

            //if ($category != NULL) {

            $slug = Urlizer::urlize($array_slug['name']) . '-' . Urlizer::urlize($array_slug['apellido_paterno']) . '-' . Urlizer::urlize($array_slug['apellido_materno']);
            $contador = 0;
            $slug_final = $slug;
            while ($this->slugValidate($slug_final)) {
                $slug_final = $slug . ++$contador;
            }
            return array(
                'slug' => TRUE,
                'value' => $slug_final,
            );
            //}
        }
        return array(
            'slug' => FALSE,
            'value' => ''
        );

    }

    /**
     * @ApiDoc(
     *     section = "User",
     *     description = "Retrieve all rolls translated",
     *     headers = {
     *       {"name" = "Authorization", "dataType"="string", "required"=true, "description"="token authorization"}
     *     }
     * )
     */
    public
    function getRolesTranslatedAction()
    {
        $helpers = $this->get("app.helpers");
        $app_users = $this->get("app.users");
        $all_roles = $app_users->getAllRolls();

        foreach ($all_roles as $key => $val) {
            foreach ($val as $k => $v) {
                $all_roles[$key][$k] = array($v, $this->get('translator')->trans($v));
            }
        }

        return $helpers->json($all_roles);
    }

    /**
     * @ApiDoc(
     *  section = "User",
     *  description="Get permissions of a profile",
     *     requirements={
     *      {"name"="profile", "dataType"="string", "default"="ROLE_SUPER_ADMIN | ROLE_EDITOR | ROLE_REDACTOR | ROLE_REDACTORJR | ROLE_BECARIOAUXILIAR", "required"=true, "description"="User's profile"}
     *    },
     *     headers = {
     *       {"name" = "Authorization", "dataType"="string", "required"=true, "description"="token authorization"}
     *     }
     * )
     */
    public function getProfileAction($profile = null)
    {
        $result = array();
        $helpers = $this->get("app.helpers");

        $security = $this->get("app.config_provider");
        $access_control = $security->getConfiguration();
        if ($profile != NULL) {
            foreach ($access_control as $key => $val) {
                if (gettype($val["roles"]) == "array") {
                    $a = array_slice($val["roles"], 0, -1);       //all items except last one
                    if (in_array($profile, $a)) {
                        $item = array();
                        $b = end($val["roles"]);                                //last one
                        $item = array(
                            "role" => $b,
                            "description" => $this->get('translator')->trans($b),
                            "group" => $this->getRoleGroup($b, true)
                        );
                        array_push($result, $item);
                    }
                }
            }
        }
        if (count($result) == 0) {
            $data = array(
                "status" => "failure",
                "code" => 400,
                "msg" => "Users profile",
                "result" => $result
            );
        } else {
            $data = array(
                "status" => "success",
                "code" => 200,
                "msg" => "Users profile",
                "result" => $result
            );
        }

        return $helpers->json($data);
    }

    /**
     * @ApiDoc(
     *  section = "User",
     *  description="Get user permissions (path, role, description, ifIsCustomized)",
     *     requirements={
     *      {"name"="id", "dataType"="integer", "default"="0", "required"=true, "description"="User's id"}
     *    },
     *     headers = {
     *       {"name" = "Authorization", "dataType"="string", "required"=true, "description"="token authorization"}
     *     }
     * )
     */
    public function getPermissionsAction($id = 0)
    {
        $helpers    = $this->get("app.helpers");
        $myRoles    = array();
        $result     = array();
        $mySections = array();
        $profile    = array();

        $em             = $this->getDoctrine()->getManager();
        $user           = $em->getRepository('BackendBundle:WfUser')->find($id);
        $myRoles        = $user->getRoles();
        $security       = $this->get("app.config_provider");
        $role_hierarchy = $security->getRoleHierarchy();
        $profiles       = $role_hierarchy['ROLE_SUPER_ADMIN'];
        $tmp            = $this->getRoleArray( $id );
        //Get sections
        $idSeccion = "_SECCION_";
        foreach ($myRoles as $k => $v) {
            if (strpos($v, $idSeccion) !== FALSE) {
                $desc = $this->getSectionDescription($v) == "" ? $v : $this->getSectionDescription($v);
                $mySections[] = array(
                    "key" => $v,
                    "description" => $desc,
                    //"description" => $this->get('translator')->trans($v),
                );
            }
        }
        //Get my profile
        foreach ($myRoles as $k => $v) {
            foreach ($profiles as $prof) {
                if (strpos($v, $prof) !== FALSE) {
                    $profile[] = array(
                        "key" => $v,
                        "description" => $this->get('translator')->trans($v),
                    );
                }
            }
        }
        $profile = array_map("unserialize", array_unique(array_map("serialize", $profile)));
        $result = array(
            "paths" => $tmp,
            "sections" => $mySections,
            "profile" => $profile
        );
        $data = array(
            "status" => "success",
            "code" => 200,
            "msg" => "User rolls",
            "result" => $result

        );

        return $helpers->json($data);
    }

    /**
     * Descripcion especifica de este metdo
     *
     * @ApiDoc(
     *     section = "User",
     *     description="Get all permissions not assigned to user (all minus current perms)",
     *     requirements={
     *      {"name"="id",      "dataType"="string", "required"=false, "description"="id user"},
     *      {"name"="profile", "dataType"="string", "required"=false, "description"="Profile"}
     *    },
     *    headers={
     *      {"name"="Authorization", "dataType"="string", "required"=true, "description"="token authorization"}
     *    }
     * )
     *
     */
    public function getPermsNotAssignedAction(Request $request)
    {
        $helpers = $this->get("app.helpers");
        $AppUsers = $this->get("app.users");
        $security = $this->get("app.config_provider");
        $access_control = $security->getConfiguration();
        $myRoles = array();
        $result = array();
        $misecurity = null;

        $id = $request->get("id", null);
        $profile = $request->get("profile", null);
        //Get roles accord request
        if ($id != NULL) {                                                     //By User's id
            $em = $this->getDoctrine()->getManager();
            $user = $em->getRepository('BackendBundle:WfUser')->find($id);
            $role_db = $user->getRoles();
            foreach ($access_control as $key => $val) {
                if (gettype($val["roles"]) == "array") {
                    $a = array_slice($val["roles"], 0, -1);      //all items except last one
                    $c = array_diff($role_db, $a);
                    if (count($c) == 0) {
                        $role = end($val["roles"]);                           //last one
                        array_push($myRoles, $role);
                    }
                }
            }

        } else if ($profile != NULL) {                                           //By profile
            foreach ($access_control as $key => $val) {
                if (gettype($val["roles"]) == "array") {
                    $a = array_slice($val["roles"], 0, -1);      //all items except last one
                    if (in_array($profile, $a)) {
                        $b = end($val["roles"]);                              //last one (role)
                        array_push($myRoles, $b);
                    }
                }
            }
        }
        //Extract diff of All roles minus myRoles
        foreach ($access_control as $key => $val) {
            if (gettype($val["roles"]) == "array") {
                $b = end($val["roles"]);                           //last one (role)
                if (!in_array($b, $myRoles)) {
                    $b = end($val["roles"]);                                //last one
                    $item = array(
                        "role" => $b,
                        "description" => $this->get('translator')->trans($b),
                        "group" => $this->getRoleGroup($b, true)
                    );
                    array_push($result, $item);
                }
            }
        }
        $data = array(
            "status" => "success",
            "code" => 200,
            "msg" => "Permissions Not Assigned yet",
            "result" => $result

        );

        return $helpers->json($data);
    }

    private function getRoleGroup($role, $complete = false)
    {

        $prefix_sections = $this->getPrefixSections();

        foreach ($prefix_sections as $prefix) {
            if (strpos($role, $prefix) !== FALSE) {
                if ($complete) {
                    return array(
                        "key" => $prefix,
                        "description" => $this->get('translator')->trans($prefix)
                    );
                } else {
                    return $prefix;
                }
            }
        }

        return array();
    }

    private function getPrefixSections()
    {
        $security = $this->get("app.config_provider");
        $access_control = $security->getConfiguration();
        $result = array();

        foreach ($access_control as $val) {
            if (gettype($val["roles"]) == "array") {
                $a = end($val['roles']);                                                                          //Get roles
                preg_match_all('/_/', $a, $matches, PREG_OFFSET_CAPTURE);
                $b = count($matches[0]) - 1;                                                                       //Get last key
                $result[] = substr($a, 0, $matches[0][$b][1] + 1);
            }
        }
        $result = array_map("unserialize", array_unique(
                array_map("serialize", $result)
            )
        );                                                                                                          //delete duplicates

        return $result;
    }

    private function getSectionDescription($section)
    {
        $idSeccion = "ROLE_SECCION_";
        $title     = str_replace($idSeccion, "", $section);
        $title     = str_replace("_", "-", $title);
        $em        = $this->getDoctrine()->getManager();

        $category = $em->getRepository('BackendBundle:Category')->findBy(
            array("slug" => $title)
        );
        if ( count( $category ) > 0 ){
            if ( $category[0]->getParentId() > 1 ){
                $parent = $em->getRepository('BackendBundle:Category')->findBy(
                    array( "id" => $category[0]->getParentId() )
                );

                return $parent[0]->getTitle() . "-" . $category[0]->getTitle();
            }else{

                return $category[0]->getTitle();
            }
        }else{
            return "";
        }
    }

    /**
     * @desc   Retrieve security array
     * @params $id integer User id (0 for nobody)
     * @params $get_methods boolean If is necesary retrieve methods in certain points
     */
    private function getRoleArray( $id ){

        $myRoles        = array();
        $result         = array();
        $em             = $this->getDoctrine()->getManager();
        $security       = $this->get("app.config_provider");
        $access_control = $security->getConfiguration();

        if( $id > 0 ){
            $user    = $em->getRepository('BackendBundle:WfUser')->find($id);
            $myRoles = $user->getRoles();
        }else{
            $AppUsers = $this->get("app.users");
            $myRoles  = $AppUsers->getUserRolls();
        }

        foreach ($access_control as $key => $val) {
            foreach ($myRoles as $v) {
                if (gettype($val["roles"]) == "string") {
                    if ($val["roles"] == $v) {
                        $endpoint = $this->getEndpoint( $val, $v );
                        array_push($result, $endpoint);
                    }
                } else if (gettype($val["roles"]) == "array") {
                    foreach ($val["roles"] as $va) {
                        if ($v == $va) {
                            $endpoint = $this->getEndpoint( $val, $v );
                            array_push($result, $endpoint);
                        }
                    }
                }
            }
        }
        //Avoid duplicates, especially for elements with more than one method
        $result = array_map("unserialize", array_unique(array_map("serialize", $result)));
        //take off keys in result
        $tmp = array();
        foreach ($result as $key => $val) {
            $tmp[] = $val;
        }

        return $tmp;
    }

    private function getEndpoint( $elem, $alt_elem ){
        $result = array();

        if (isset($elem["methods"])) {
            $result = array(
                        "path"   => $elem["path"],
                        "method" => $elem["methods"],
                        "role"   => array(
                            "key"         => end($elem["roles"]),
                            "description" => $this->get('translator')->trans(end($elem["roles"])),
                            "customized"  => $alt_elem == end($elem["roles"]) ? "true" : "false"
                        )
                     );
        } else {
            if (!in_array($elem["path"], $result)) {
                $result = array(
                            "path"   => $elem["path"],
                            "method" => array(),
                            "role"   => array(
                                "key"         => end($elem["roles"]),
                                "description" => $this->get('translator')->trans(end($elem["roles"])),
                                "customized"  => $alt_elem == end($elem["roles"]) ? "true" : "false"
                            )
                          );
            }
        }

        return $result;
    }
}
