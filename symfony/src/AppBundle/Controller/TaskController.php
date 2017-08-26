<?php
namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use BackendBundle\Entity\Task;
use AppBundle\Services\Helpers;
use AppBundle\Services\JwtAuth;

class TaskController extends Controller{

	public function newAction(Request $request, $id = null){
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

            		if ($id == null) {
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
            			//comprobar permisos de edicion de tarea
            			$task = $em->getRepository('BackendBundle:Task')->findOneBy(array(
            				'id' => $id
            			));
            			if (isset($identity->sub) && $identity->sub == $task->getUser()->getId()) {
            				
            				$task->setTitle($title);
		            		$task->setDescription($description);
		            		$task->setStatus($status);
		            		$task->setUpdatedAt($updatedAt);
		            		$em->persist($task);
							$em->flush();
		            		$data = array(
								'status' => 'success',
								'code' => 200,
								'msg' => 'Task Updated!',
								'data' => $task
							);
            			} else {
            				$data = array(
								'status' => 'error',
								'code' => 400,
								'msg' => 'Task Updated error, you not ownwer!'
			 				);
            			}
            			
            		}
            		

            		 
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

    public function tasksAction(Request $request){
        $helpers = $this->get(Helpers::class);
        $jwt_auth = $this->get(JwtAuth::class);
        $token = $request->get('authorization', null);
        $authCheck = $jwt_auth->checkToken($token);

        if ($authCheck) {
            $identity = $jwt_auth->checkToken($token, true);
            $em = $this->getDoctrine()->getManager();
            $dql = "SELECT t FROM BackendBundle:Task t WHERE t.user = {$identity->sub} ORDER BY t.id DESC";
            $query = $em->createQuery($dql);
            $page = $request->query->getInt('page',1);
            $paginator = $this->get('knp_paginator');
            $items_per_page = 10;

            $pagination = $paginator->paginate($query, $page, $items_per_page);
            $total_items_count = $pagination->getTotalItemCount();
            $total_pages = ceil($total_items_count / $items_per_page);

            $data = array(
                'status' => 'success',
                'code' => 200,
                'total_items_count' => $total_items_count,
                'page_actual' => $page,
                'items_per_page' => $items_per_page,
                'total_pages' => $total_pages,
                'data' => $pagination
             
            );
        }else {
            $data = array(
                'status' => 'error',
                'code' => 400,
                'msg' => 'Athorization not valid'
            );
        }
        return $helpers->json($data);
    }

    public function taskAction(Request $request, $id = null){
        $helpers = $this->get(Helpers::class);
        $jwt_auth = $this->get(JwtAuth::class);
        $token = $request->get('authorization', null);
        $authCheck = $jwt_auth->checkToken($token);

        if ($authCheck) {
            $identity = $jwt_auth->checkToken($token, true);

            $em = $this->getDoctrine()->getManager();

            $task = $task = $em->getRepository('BackendBundle:Task')->findOneBy(array('id' => $id ));

            if ($task && is_object($task) && $identity->sub == $task->getUser()->getId()) {
                $data = array(
                    'status' => 'success',
                    'code' => 200,
                    'data' => $task
                );
            } else {
                $data = array(
                    'status' => 'error',
                    'code' => 404,
                    'msg' => 'Task not foud'
                );
            }
            
        }else {
            $data = array(
                'status' => 'error',
                'code' => 400,
                'msg' => 'Athorization not valid'
            );
        }
        return $helpers->json($data);
    }

    public function searchAction(Request $request, $search = null){
        $helpers = $this->get(Helpers::class);
        $jwt_auth = $this->get(JwtAuth::class);
        $token = $request->get('authorization', null);
        $authCheck = $jwt_auth->checkToken($token);

        if ($authCheck) {
            $identity = $jwt_auth->checkToken($token, true);

            $em = $this->getDoctrine()->getManager();

            //filtro
            $filter = $request->get('filter', null);
            if (empty($filter)) {
                $filter = null;
            } elseif ($filter == 1) {
                 $filter = 'new';
            } elseif ($filter == 2) {
                 $filter = 'todo';
            } else {
                 $filter = 'finished';
            }
            
            // Ordenacion
            $order = $request->get('order', null);
            if (empty($order) || $order == 2) {
                $order = 'DESC';
            } else {
                $order = 'ASC';
            }
            

            //Busqueda
            if ($search != null) {
                $dql = "SELECT t FROM BackendBundle:Task t WHERE t.user = $identity->sub AND (t.title LIKE :search OR t.description LIKE :search)";
            } else {
                $dql = "SELECT t FROM BackendBundle:Task t WHERE t.user = $identity->sub"; 
            }

            //set Filter
            if ($filter != null) {
                $dql .= " AND t.status = :filter";
            }
            
            //Order
            $dql .= " ORDER BY t.id $order";
               
            $query = $em->createQuery($dql);

            if ($filter != null) {
                $query->setParameter('filter', "$filter");
            }

            if (!empty($search)) {
                $query->setParameter('search', "%$search%");
            }

            $tasks = $query->getResult();
            $data = array(
                'status' => 'success',
                'code' => 200,
                'sub' => $identity->sub,
                'filter' => $filter,
                'order' => $order,
                'dql' => $dql,
                'data' => $tasks
            );
        }else {
            $data = array(
                'status' => 'error',
                'code' => 400,
                'msg' => 'Athorization not valid'
            );
        }
        return $helpers->json($data);
    }

    public function removeAction(Request $request, $id = null){
        $helpers = $this->get(Helpers::class);
        $jwt_auth = $this->get(JwtAuth::class);
        $token = $request->get('authorization', null);
        $authCheck = $jwt_auth->checkToken($token);

        if ($authCheck) {
            $identity = $jwt_auth->checkToken($token, true);

            $em = $this->getDoctrine()->getManager();

            $task = $task = $em->getRepository('BackendBundle:Task')->findOneBy(array('id' => $id ));

            if ($task && is_object($task) && $identity->sub == $task->getUser()->getId()) {
                $em->remove($task);
                $em->flush();
                $data = array(
                    'status' => 'success',
                    'code' => 200,
                    'msg' => 'Task deleted',
                    'data' => $task
                );
            } else {
                $data = array(
                    'status' => 'error',
                    'code' => 404,
                    'msg' => 'Task not foud'
                );
            }
            
        }else {
            $data = array(
                'status' => 'error',
                'code' => 400,
                'msg' => 'Athorization not valid'
            );
        }
        return $helpers->json($data);
    }
}