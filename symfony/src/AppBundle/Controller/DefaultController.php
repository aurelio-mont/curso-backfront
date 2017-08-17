<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use AppBundle\Services\Helpers;
use AppBundle\Services\JwtAuth;


class DefaultController extends Controller
{
    
    public function indexAction(Request $request)
    {
        // replace this example code with whatever you need
        return $this->render('default/index.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
        ]);
    }


    public function loginAction(Request $request){
        $helpers = $this->get(Helpers::class);

        //Recibir Json por POST
        $json = $request->get('json', null);

        //Array a devolver por defecto
        $data = array(
            'status' => 'error',
            'data' => 'Send JSON via POST!'
        );

        if ($json) {
            // me haces el login


            //Pasar la infomacion recibida a un objeto php
            $params = json_decode($json);

            $email = (isset($params->email)) ? $params->email : null;
            $password = (isset($params->password)) ? $params->password : null;
            $getHash = (isset($params->getHash)) ? $params->getHash : null;

            $emailConstraint = new Assert\Email();
            $emailConstraint->message = 'This email isn not valid';
            $validate_email = $this->get('validator')->validate($email, $emailConstraint);

            if ($email != null && count($validate_email) == 0 && $password != null) {
                
                $jwt_auth = $this->get(JwtAuth::class);

                if ($getHash == null || $getHash == false) {
                    $singup = $jwt_auth->singup($email, $password);
                } else {
                    $singup = $jwt_auth->singup($email, $password, true);
                }
                return $this->json($singup);

            }else{
                $data = array(
                    'status' => 'error',
                    'data' => 'Email or password is incorrect'
                );
            }
        }

        return $helpers->json($data);
        

    }

    public function pruebasAction()
    {
        $em = $this->getDoctrine()->getManager();
        $userRepo = $em->getRepository('BackendBundle:User');
        $users = $userRepo->findAll();

        $helpers = $this->get(Helpers::class);
        return $helpers->json($users);
    }
}
