<?php
namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use BackendBundle\Entity\Task;
use AppBundle\Services\Helpers;
use AppBundle\Services\JwtAuth;

class TaskController extends Controller{

	public function newAction(Request $request){
		$helpers = $this->get(Helpers::class);
        $jwt_auth = $this->get(JwtAuth::class);
        $token = $request->get('authorization', null);
        $authCheck = $jwt_auth->checkToken($token);

        if ($authCheck) {
        	$identity = $jwt_auth->checkToken($token, true);
        	$json = $request->get('json', null);
        	if ($json != null) {
        		
        		$params = json_decode($json);
        		$createdAt = new \Datetime('now');
        		$updatedAt = new \Datetime('now');
        		$user_id = (isset($identity->sub)) ? $identity->sub : null;
        		$title = (isset($params->title)) ? $params->title : null;
        		$description = (isset($params->description)) ? $params->description : null;
        		$status = (isset($params->status)) ? $params->status : null;

        		if ($user_id != null && $title != null) {
        			//crear tarea
        			$em = $this->getDoctrine()->getManager();
        			//obtenemos el usuario de la base de datos
            		$user = $em->getRepository('BackendBundle:User')->findOneBy(array(
            			'id' => $user_id
            		));
            		$task = new Task();
            		$task->setUser($user);
            		$task->setTitle($title);
            		$task->setDescription($description);
            		$task->setStatus($status);
            		$task->setCreatedAt($createdAt);
            		$task->setUpdatedAt($updatedAt);
            		$em->persist($task);
					$em->flush();
            		$data = array(
						'status' => 'success',
						'code' => 200,
						'msg' => 'Task crated!',
						'data' => $task
					); 
        		} else {
        			$data = array(
						'status' => 'error',
						'code' => 400,
						'msg' => 'Task not created, validation failed !'
			 		);
        		}
        		

        		
			 	
        	} else {
        		$data = array(
					'status' => 'error',
					'code' => 400,
					'msg' => 'Task not created, params failed'
			 	);
        	}
			       	
        } else {
        	$data = array(
				'status' => 'error',
				'code' => 400,
				'msg' => 'Athorization not valid'
			 );
        }
        
        return $helpers->json($data);
	}
}