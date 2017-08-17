<?php
namespace AppBundle\Services;

use Symfony\Component\Serializer\Normalizer;
use Symfony\Component\Serializer\Serializer\Encoder;
use Symfony\Component\Serializer;
use Symfony\Component\HttpFoundation;

/**
helper
*/
class Helpers{

	public $manager;
	
	function __construct($manager){
		$this->manager = $manager;
	}

	public function holaMundo()
	{
		return 'hola mundo desde el servio de Symfony';
	}

	public function json($data){
		$normalizers = array(new \Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer());
		$encoders = array('json' => new \Symfony\Component\Serializer\Encoder\JsonEncoder() );
		$serializer = new \Symfony\Component\Serializer\Serializer($normalizers, $encoders);
		$json = $serializer->serialize($data, 'json');

		$response = new \Symfony\Component\HttpFoundation\Response();
		$response->setContent($json);
		$response->headers->set('Content-Type', 'application/json');

		return $response;
	}
}