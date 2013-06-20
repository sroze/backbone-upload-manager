<?php

namespace SRozeIO\UploadHandlerBundle\Controller;

use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;

use Symfony\Component\HttpFoundation\StreamedResponse;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Symfony\Component\Finder\Finder;

use SRozeIO\UploadHandlerBundle\DependencyInjection\Configuration;

use Symfony\Component\HttpFoundation\JsonResponse;

use SRozeIO\UploadHandlerBundle\Exception\UploadException;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class DefaultController extends Controller
{
    /**
     * Upload a new file.
     * 
     * @Route("/upload", name="file_upload")
     * @Method("POST")
     */
    public function uploadAction()
    {
        $this->clearFiles();
        
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
    
    /**
     * List uploaded files.
     * 
     * @Route("/files", name="file_list")
     * @Method("GET")
     */
    public function filesAction ()
    {
        $this->clearFiles();
        
        $finder = $this->getFinder();
        $files = array();
        
        foreach ($finder->files() as $file) {
            $files[] = array(
                'name' => $file->getFilename(),
                'size' => $file->getSize(),
                'modified_time' => $file->getMTime(),
                'download_url' => $this->generateUrl('file_download', array(
                    'name' => $file->getFilename()
                ))
            );
        }
        
        return new JsonResponse($files);
    }
    
    /**
     * Download an uploaded file.
     * 
     * @Route("/file/{name}/download", name="file_download")
     * @Method("GET")
     * @param string $name
     */
    public function downloadAction ($name)
    {
        $root = $this->container->getParameter(Configuration::PARAMETER_UPLOAD_ROOT_DIR);
        $path = $root.$name;
        
        if (!file_exists($path)) {
            throw new NotFoundHttpException(sprintf('No file found with name "%s"', $name));
        }
        
        $stream = @fopen($path, 'r');
        
        // Create Response object
        $response = new StreamedResponse(function() use ($stream) {
            if ($stream == false) {
                throw new ServiceUnavailableHttpException(60, 'Unable to open file');
            }
            	
            while (!feof($stream)) {
                $buffer = fread($stream, 2048);
                echo $buffer;
                flush();
            }
            	
            fclose($stream);
        });
        
        // Set headers
        $response->headers->set('Content-disposition', 'attachment; filename='.$name);
        $response->headers->set('Content-length', filesize($path));
        $response->headers->set('Content-Type', mime_content_type($path));
    
        return $response;
    }
    
    /**
     * Create a Finder instance to fetch the upload directory.
     * 
     * @return Finder
     */
    private function getFinder ()
    {
        $root = $this->container->getParameter(Configuration::PARAMETER_UPLOAD_ROOT_DIR);
        $finder = new Finder();
        $finder->in($root);
        
        return $finder;
    }
    
    /**
     * Clear files that was uploaded more than the last
     * five minutes.
     * 
     */
    private function clearFiles ()
    {
        $finder = $this->getFinder();
        
        // For each file modified
        foreach ($finder->files()->date('< now - 5 minutes') as $file) {
            unlink($file->getRealpath());
        }
    }
}
