<?php


namespace App\Controller;

use Symfony\Component\HttpClient\CurlHttpClient;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Finder\Iterator\RecursiveDirectoryIterator;
use Symfony\Contracts\Cache\ItemInterface;


class viewController extends AbstractController
{
	
    /**
     * @Route("/load",name="proj_load")
     */
    public function load(){ // API LOAD

        $clientCurl = new CurlHttpClient();
		
		
		try
		{
			$reponse = $clientCurl->request("POST", "http://concoursphoto.ort-france.fr/api/matrice", ['headers' => ['Content-Type' => 'application/json','Accept' => 'application/json'],'body' => '{}']);
		}
		catch (Exception $t)
		{
			$errorArray['Type'] = 1;
			return new JsonResponse([
				'Event' => 'api/load',
				'Error' => 'pvf',
				'Type' => 1
			]);
		}

        $Content = $reponse->getContent();
		$resultArray = json_decode($Content, true)['results'][0];
		// Retourne le chemin du DOssier
		$varDirectory = $this->getParameter('kernel.project_dir') . '\\var'; 
        $formationsDirectory = $varDirectory . '\\formations';
        // Si le dossier n'existe pas, alors on le créer
		if (!file_exists($formationsDirectory))
		{
			mkdir($formationsDirectory);
		}
		
		$formationsCount = 0;
		$polesCount = 0;
		
		foreach ($resultArray as $currPoleKeyJson => $currPoleValueJson) {
			$poleDirectory = $formationsDirectory . '/' . $currPoleKeyJson;
			$polesCount++;
			
			try
			{
				if (!file_exists($poleDirectory))
				{
					mkdir($poleDirectory);
				}
			}
			catch (Exception $t)
			{
                // Gestion des erreurs au format Json
				$errorArray['Type'] = 1;
				return new JsonResponse([
					'Event' => 'api/load',
					'Error' => 'pvf',
					'Type' => 2
				]);
			}
			
			foreach($currPoleValueJson as $currFormationKeyJson => $currFormationValueJson)
			{
				$jsonFilePath = $poleDirectory . '\\' . $currFormationKeyJson . '.json';
				
				$formationsCount++;
				
				$formationJson = json_encode($currFormationValueJson);
				
				if (file_exists($jsonFilePath))
				{
					unlink($jsonFilePath);
				}
				
				file_put_contents($jsonFilePath, $formationJson);
			}
		}

        return new JsonResponse([
            'NbWriteFileFormation' => $formationsCount,
            'NbWriteRepoPole' => $polesCount
        ]);
    }
	
	/**
     * @Route("/view/{id}",name="proj_view")
     */


	public function view(string $id){
		$cache = new FilesystemAdapter(); // on initialise les variables

		$varDirectory = $this->getParameter('kernel.project_dir') . '\\var';
		$formationsDirectory = $varDirectory . '\\formations';
		if (!file_exists($formationsDirectory))
		{
			mkdir($formationsDirectory);
		}
		
		$directory = new \RecursiveDirectoryIterator($formationsDirectory);
		$iterator = new \RecursiveIteratorIterator($directory);
		$regex = new \RegexIterator($iterator, '/^.+\.json$/i', \RecursiveRegexIterator::ALL_MATCHES);
		
		foreach ($regex as $filePath => $val) {
			$ext = pathinfo($filePath, PATHINFO_EXTENSION);
			$fileName = basename($filePath,".".$ext);
			$cacheResult = $cache->getitem($fileName); 
			
			if(!$cacheResult->isHit()) { 
			   $fileContents = file_get_contents($filePath, true);
			   $cacheResult->set($fileContents);
			   $cache->save($cacheResult); 
			}
		}
		try{
		$stringResponse = $cache->getItem($id)->get();
        }
        catch(Exception $t) 
        {
        return new Response($stringResponse);
        }
	}

}