<?php

namespace AppBundle\Services;

use Firebase\JWT\JWT;

class JwtAuth{

	public $manager;
	public $key;

	function __construct($manager)
	{
		$this->manager = $manager;
		$this->key = 'Holaquetalsoylaclabesecreta32423';
	}

	public function singup($email, $password, $getHash = null){

		$user = $this->manager->getRepository('BackendBundle:User')->findOneBy(array(
			'email' => $email,
			'password' => $password
		));

		$singup = false;

		if (is_object($user)) {
			$singup = true;
		}

		if ($singup) {
			//GENERAR EL TOKEN JWT
			$token = array(
				'sub' => $user->getId(),
				'email' => $user->getEmail(),
				'name' => $user->getName(),
				'surname' => $user->getSurname(),
				'iat' => time(),
				'exp' => time() + (7 * 24 * 60 * 60)
			);

			$jwt = JWT::encode($token, $this->key, 'HS256');
			$decoded = JWT::decode($jwt, $this->key, array('HS256'));

			if ($getHash == null) {
				$data = $jwt;
			} else {
				$data = $decoded;
			}
			
		}else {
			$data = array(
				'status' => 'error',
				'data' => 'Login filed'
			);
		}

		return $data;
	}

	public function checkToken($jwt, $getIdentity = false){
		$auth = false;

		try {
			$decoded = JWT::decode($jwt, $this->key, array('HS256'));
		} catch (\UnexpectedValueException $e) {
			$auth = false;
		} catch (\DomainException $e){
			$auth = false;
		}

		if (isset($decoded) && is_object($decoded) && isset($decoded->sub)) {
			$auth = true;
		} else {
			$auth = false;
		}

		if ($getIdentity == false) {
			return $auth;
		} else {
			return $decoded;
		}
		
		
	}

}