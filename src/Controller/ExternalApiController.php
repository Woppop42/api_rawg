<?php

namespace App\Controller;

use App\Entity\Games;
use App\Entity\Genres;
use App\Repository\GenresRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ExternalApiController extends AbstractController
{
    #[Route('/api/external/getSfDoc', name: 'external_api', methods: 'GET')]
    public function getSfDoc(HttpClientInterface $httpClient, EntityManagerInterface $manager, GenresRepository $repo): Response
    {
        try {
            $apiKey = 'ee18b08be15945d6b49d93eaa117d2a9';
            $response = $httpClient->request(
                'GET',
                'https://api.rawg.io/api/games?page=2&page_size=100&key=' . $apiKey, 
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
                foreach ($json['results'] as $gameData)
                {
                    $game = new Games;
                    $game->setName($gameData['name']);
                    $game->setDateSortie($gameData['released']);
                    $game->setImage($gameData['background_image']);
                    if(isset($gameData['genres']) && is_array($gameData['genres'])) {
                        foreach ($gameData['genres'] as $genreData)
                        {
                            $genreName = $genreData['name'];
            
                            // Vous pouvez récupérer l'entité Genres à partir de la base de données par son nom
                            $genre = $repo->findOneBy(['name' => $genreName]);
            
                            // Si l'entité Genres n'existe pas encore, vous pouvez la créer
                            if (!$genre) {
                                $genre = new Genres();
                                $genre->setName($genreName);
                            }
            
                            $game->addGenre($genre);
                        }
                    }
                    $manager->persist($game);
                    
                }
                $manager->flush();
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
