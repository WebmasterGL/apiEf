<?php

/**
 * Created by PhpStorm.
 * User: danielsolis
 * Date: 20/06/17
 * Time: 17:19
 */
namespace AppBundle\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
class TokenAuthenticator extends AbstractGuardAuthenticator
{
    public $helpers;
    private $currentUri;

    public function __construct($helpers){

        $this->helpers = $helpers;

    }

    /**
     * Called on every request. Return whatever credentials you want,
     * or null to stop authentication.
     */
    public function getCredentials(Request $request)
    {

        $this->currentUri =  $request->getMethod().'|'.$request->getRequestUri().'|'.json_encode($request->request->all());


        if($request->headers->has('Authorization')){
           $header =  $request->headers->get('Authorization');
        }else{
            return false; //Si devolvemos un null, provocaremos un agujero en el firewall
        }



        $token = explode(" " ,$header);


        /*if (!$token = $request->headers->get('Authorization')) {

            // no token? Return null and no other methods will be called
            var_dump("No token");
            return false; //Si devolvemos un null, provocaremos un agujero en el firewall
        }*/



        //if(isset($identity->user)){
            return array(
                'token' => $token[1]
            );
        //}




        // What you return here will be passed to getUser() as $credentials

    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $helpers = $this->helpers;
        $token = $credentials['token'];
        $identity = $helpers->authCheck($token, true); //obtenemos el objeto del usuario encriptado en el token que llega

        if(isset($identity->user)){

            $helpers->logActivity($identity->email, $this->currentUri);

            return $userProvider->loadUserByUsername($identity->user);  //Esta clase va a buscar el usuario en su Clase configurada de usuarios

        }
        else if($identity<0 ) {
            $helpers->logActivity( "Token Expired", $token);
            $response = new Response(" "); //Si está vacio o null se salta a la excepción innata (DSG: 17/08/2017)
            $response->setStatusCode(410,"Token Expired");
            $response->send();
        }
        else if( !$identity){
            $helpers->logActivity( "!$identity", $token);
            return null;
        }



    }

    public function checkCredentials($credentials, UserInterface $user)
    {


        $helpers = $this->helpers;

        $valid = $helpers->authCheck($credentials['token']);

        if($valid<0)
            return false;
        else
            return $valid;


        // return true to cause authentication success
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {

        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {

        $data = array(
            'message' => strtr($exception->getMessageKey(), $exception->getMessageData())

            // or to translate this message
            // $this->translator->trans($exception->getMessageKey(), $exception->getMessageData())
        );

        return new JsonResponse($data, Response::HTTP_FORBIDDEN);
    }

    /**
     * Called when authentication is needed, but it's not sent
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        $data = array(
            // you might translate this message
            'message' => 'Authentication Required'
        );

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }

    public function supportsRememberMe()
    {
        return false;
    }
}
