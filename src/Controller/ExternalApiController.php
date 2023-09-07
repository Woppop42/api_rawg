<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ExternalApiController extends AbstractController
{
    #[Route('/api/external/getSfDoc', name: 'external_api', methods: 'GET')]
    public function getSfDoc(HttpClientInterface $httpClient): Response
    {
        try {
            $apiKey = 'ee18b08be15945d6b49d93eaa117d2a9';
            $response = $httpClient->request(
                'GET',
                'https://api.rawg.io/api/genres?key=' . $apiKey, 
                [
                    'headers' =>[
                        'Authorization' => 'Bearer' . $apiKey,
                        'Accept' => 'application/json'   
                    ]
                ]
            );

            // Vérifiez le code de statut de la réponse
            if ($response->getStatusCode() === 200) {
                // Si la réponse est OK, renvoyez les données JSON
                $json = json_decode($response->getContent(), true);
                return $this->render('external_api/index.html.twig', [
                    'json' => $json,
                ]);
            } else {
                // Gérez d'autres codes de statut ici si nécessaire
                $error =  new JsonResponse(['error' => 'An error occured.'], $response->getStatusCode());
                return $this->render('external_api/index.html.twig', [
                    'error' => $error
                ]);
            }
        } catch (\Exception $e) {
            // Capturez et gérez les exceptions ici
            $error =  new JsonResponse(['error' => 'An error occured.'], $response->getStatusCode());
                return $this->render('external_api/index.html.twig', [
                    'error' => $error
                ]);
        }
    }
}
