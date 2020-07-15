<?php


namespace App\Controller;

use Symfony\Component\HttpClient\CurlHttpClient;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Config\Definition\Exception\Exception;

class viewController extends AbstractController
{

    /**
     * @Route("/view",name="proj_view")
     */
    public function view(){

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
        
        $varDirectory = $this->getParameter('kernel.project_dir') . '/var';
        $formationsDirectory = $varDirectory . '/formations';
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
                $errorArray['Type'] = 1;
                return new JsonResponse([
                    'Event' => 'api/load',
                    'Error' => 'pvf',
                    'Type' => 2
                ]);
            }
            
            foreach($currPoleValueJson as $currFormationKeyJson => $currFormationValueJson)
            {
                $jsonFilePath = $poleDirectory . '/' . $currFormationKeyJson . '.json';
                
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
     * @Route("/",name="hello")
     */
    public function hello(){

        return new Response('ORT - PROJET API');
    }

}