<?php
/**
 * Created by PhpStorm.
 * User: javiermorquecho
 * Date: 26/06/17
 * Time: 13:25
 */

namespace AppBundle\Services;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class Users
{
    private $container;
    protected $auth_check;
    private $tokenStorage;
    private $manager;

    public function __construct(ContainerInterface $container, AuthorizationCheckerInterface $authorizationChecker, TokenStorage $tokenStorage, $manager)
    {
        $this->container  = $container;
        $this->auth_check = $authorizationChecker;
        $this->tokenStorage = $tokenStorage;
        $this->manager = $manager;
    }

    public function getUserRolls()
    {
        $allRoles   = $this->container->getParameter('security.role_hierarchy.roles');
        $myRoles    = array();

        foreach( $allRoles as $key => $val ){
            if ( $this->auth_check->isGranted( $key ) ){
                array_push( $myRoles, $key );
                foreach( $val as $v ){
                    if ( $this->auth_check->isGranted( $v ) ){
                        array_push( $myRoles, $v );
                    }
                }
            }else{
                foreach( $val as $v ){
                    if ( $this->auth_check->isGranted( $v ) ){
                        array_push( $myRoles, $v );
                    }
                }
            }
        }
        //add subroles of my parent's roles
        foreach ( $myRoles as $key => $value ) {
            foreach ($allRoles as $key2 => $value2) {
                if ( $value == $key2 ){
                    array_merge( $myRoles, $value2);
                }
            }
        }
        $myRoles = array_unique( $myRoles );

        return $myRoles;
    }

    public function getCategoriesUserLogged($validatingCategory=NULL)
    {
        $all = false;
        $category_array = array();
        $isValidCategory = false;

        $myRoles = $this->tokenStorage->getToken()->getUser()->getRoles();


        foreach($myRoles as $myRol){

            if(strpos($myRol,"ROLE_ADMIN")!==false || strpos($myRol,"ROLE_SUPER_ADMIN")!==false){

                $all = true;
                break;
            }
            if(strpos($myRol,"SECCION")!==false){ //SOLO se procesan los roles de las secciones asignadas
                $localArray = explode("ROLE_SECCION_",$myRol);

                $localArray[1] = str_replace("_","-",$localArray[1]);


                $category = $this->manager->getRepository('BackendBundle:Category')->findOneBy(['slug' => strtolower($localArray[1]) ]);

                //if($category!=null){

                if(count($category)>0){

                    array_push($category_array,$category);

                    if($validatingCategory!=NULL && $validatingCategory == $category->getId())
                    {
                        $isValidCategory = true;
                    }
                }
            }
        }

        $data = array(  "fullAccess" => $all,
                        "categories" => $category_array,
                        "isValidCategory" =>$isValidCategory
        );

        return $data;
    }

    public function getAllRolls()
    {
        $allRoles = $this->container->getParameter('security.role_hierarchy.roles');

        return $allRoles;
    }

    public function getCurrentUser(){
        //Get current logged user
        $user = $this->tokenStorage->getToken()->getUser();
        $user_current_id = $user->getId();

        return $user_current_id;
    }

}
