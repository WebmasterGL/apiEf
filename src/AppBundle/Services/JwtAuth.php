<?php

namespace AppBundle\Services;

use Firebase\JWT\JWT;

use Symfony\Component\HttpFoundation\Response;

/**
 * Created by PhpStorm.
 * User: danielsolis
 * Date: 14/06/17
 * Time: 13:47
 */
class JwtAuth
{
    public $manager;
    public $encoder;
    public $key;
    public $ttl;


    public function __construct($manager, $encoder, $clave_secreta, $ttl)
    {
        $this->manager = $manager;
        $this->encoder = $encoder;

        $this->key = $clave_secreta;
        $this->ttl = (int)$ttl; //forzando al tipo entero, si no, pasa como string




    }


    public function signup($usr, $password, $getHash = NULL)
    {
        $key = $this->key;

        $user = $this->manager->getRepository('BackendBundle:WfUser')
            ->findOneBy(['username' => $usr]);

        if (!$user) {
            return false;
        }

        /* $isValid = $this->get('security.password_encoder')
             ->isPasswordValid($user, $password);*/

        $factory = $this->encoder;
        $encoder = $factory->getEncoder($user);
        $salt = $user->getSalt();

        $isValid = $encoder->isPasswordValid($user->getPassword(), $password, $salt);

        if (!$isValid) {
            return false;
        }

        if ($isValid && $user->getEnabled() && !$user->getCredentialsExpired()) {

            $token = array(
                "sub" => $user->getId(),
                "user" => $user->getUsername(),
                "email" => $user->getEmail(),
                "name" => $user->getFirstName(),
                "apellido_paterno" => $user->getAPaterno(),
                "apellido_materno" => $user->getAMaterno(),
                "job" => $user->getJob(),
                "iat" => time(),
                "exp" => time() + $this->ttl //(7*24*60*60)
            );

            $jwt = JWT::encode($token, $key, 'HS256');

            $decoded = JWT::decode($jwt, $key, array('HS256'));

            if ($getHash != NULL) {
                return $jwt;
            } else {
                return $decoded;
            }

        } else {
            $response = new Response();
            $response->setStatusCode(400);
            $response->setContent("error");
            $response->setContent( json_encode( array( "Login failed, BD Response!!") ) );
        }
    }

    public function checkToken($jwt, $getIdentity = false)
    {
        $key = $this->key;

        $auth = false;

        $decoded = "";

        try {
            $decoded = JWT::decode($jwt, $key, array('HS256'));

        } catch (\Firebase\JWT\ExpiredException $e) {
            $auth = -1;
        } catch (\UnexpectedValueException $e) {
            $auth = false;

        } catch (\DomainException $e) {
            $auth = false;

        }


        if (isset($decoded->sub)) { //su es el id del usuario
            $auth = true;

        }/*else{
            $auth = false;
        }*/


        if ($getIdentity == true && $auth > 0) { // >0 significa preguntar por un true;
            return $decoded;
        } else {
            return $auth;
        }

    }

    public function encodePwd($user, $password, $salt)
    {

        $factory = $this->encoder;
        $encoder = $factory->getEncoder($user);
        $pwd = $encoder->encodePassword($password, $salt);

        return $pwd;

    }
}
