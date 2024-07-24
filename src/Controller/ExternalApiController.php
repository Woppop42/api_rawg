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
    #[Route('/api/external/getRawgGame', name: 'external_api', methods: 'GET')]
    public function getSfDoc(HttpClientInterface $httpClient, EntityManagerInterface $manager, GenresRepository $repo): Response
    {
        try {
            $apiKey = 'token';
            $response = $httpClient->request(
                'GET',
                'https://api.rawg.io/api/games?page=4&page_size=100&key=' . $apiKey, 
                [
                    'headers' =>[
                        'Authorization' => 'Bearer' . $apiKey,
                        'Accept' => 'application/json',
                        'genres' => 'multiplayer'   
                    ]
                ]
            );

            
            if ($response->getStatusCode() === 200) {
                
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
            
                            
                            $genre = $repo->findOneBy(['name' => $genreName]);
            
                            
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
                
                $error =  new JsonResponse(['error' => 'An error occured.'], $response->getStatusCode());
                return $this->render('external_api/index.html.twig', [
                    'error' => $error
                ]);
            }
        } catch (\Exception $e) {
            
            $error =  new JsonResponse(['error' => 'An error occured.'], $response->getStatusCode());
                return $this->render('external_api/index.html.twig', [
                    'error' => $error
                ]);
        }
    }
}
