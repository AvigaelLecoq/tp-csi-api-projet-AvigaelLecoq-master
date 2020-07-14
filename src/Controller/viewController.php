<?php


namespace App\Controller;

use Symfony\Component\HttpClient\CurlHttpClient;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class viewController extends AbstractController
{

    /**
     * @Route("/view",name="proj_view")
     */
    public function view(){

        $clientCurl = new CurlHttpClient();

        $reponse = $clientCurl->request("POST", "https://concoursphoto.ort-france.fr/api/matrice",
            ['headers' => ['Content-Type' => 'application/json','Accept' => 'application/json'],'body' => '{}']);

        $Content = $reponse->getContent();

        dd($Content);

        return new Response('ORT - PROJET API');
    }

    /**
     * @Route("/",name="hello")
     */
    public function hello(){

        return new Response('ORT - PROJET API');
    }

}