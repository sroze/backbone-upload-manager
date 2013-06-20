<?php

namespace SRozeIO\UploadHandlerBundle\Controller;

use SRozeIO\UploadHandlerBundle\DependencyInjection\Configuration;

use Symfony\Component\HttpFoundation\JsonResponse;

use SRozeIO\UploadHandlerBundle\Exception\UploadException;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class DefaultController extends Controller
{
    /**
     * @Route("/upload")
     * @Method("POST")
     */
    public function uploadAction()
    {
        try {
	        // Handle file uploads
    	    $files = $this->get('srozeio.upload_handler')->handle(array(
    	        'root_path' => $this->container->getParameter(Configuration::PARAMETER_UPLOAD_ROOT_DIR),
    	        'param_name' => 'files'
    	    ));
    	    
    	    // Return successfull response
    	    return new JsonResponse($files);
	    } catch (UploadException $e) {
	        return new JsonResponse(array(
	            'error' => $e->getMessage()
	        ), 400);
	    }
    }
}
