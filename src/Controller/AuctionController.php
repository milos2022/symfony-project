<?php

namespace App\Controller;

use DateTimeImmutable;
use App\Entity\Auction;
use App\Form\AuctionType;
use App\Service\FileUploader;
use App\Repository\BuyerRepository;
use App\Repository\AuctionRepository;
use App\Service\Algorithm;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;

class AuctionController extends AbstractController
{

    #[Route('/auction', name: 'auction_list')]
    public function index(AuctionRepository $auctions): Response
    {
        return $this->render(
            'auction/index.html.twig',
            [
                'auctions' => $auctions->findAll()
            ]
        );
    }

    #[Route('/auction/{auction}/view', name: 'auction_view')]
    public function view(Auction $auction): Response
    {
        return $this->render(
            'auction/view.html.twig',
            [
                'auction' => $auction,
                'buyers' => $auction->getBuyers()
            ]
        );
    }

    #[Route('/auction/add', name: 'auction_add')]
    public function add(Request $request, FileUploader $fileUploader, AuctionRepository $auctions): Response
    {
        $auction = new Auction;
        $form = $this->createForm(AuctionType::class, $auction);
        
        $form->handleRequest($request); 
        if ($form->isSubmitted() && $form->isValid()) { 
            $jsonFile = $form->get('importFilename')->getData();

            if($jsonFile) {
                $jsonFileName = $fileUploader->upload($jsonFile);

                $auction->setImportFilename($jsonFileName);
                $auction->setCreatedAt(new DateTimeImmutable());
                $auctions->save($auction, true);
                
                $this->addFlash('success', "Auction added successfuly");

                return $this->redirectToRoute('auction_list');
            }

        } else { 
            return $this->render('auction/add.html.twig', array( 
                'form' => $form, 
            )); 
        } 
    }

    #[Route('/auction/{auction}/run', name: 'auction_run')]
    public function run(Auction $auction, Algorithm $algorithm, BuyerRepository $buyers): RedirectResponse
    {
        if(is_null($auction->getClosedAt()) )
        {
            $response = $algorithm->run($auction); 
            if ($response->getStatus() == 'success' )

                $winBuyer = $buyers->findOneBy(["name" => $response->getBuyerName(), "Auction" => $auction->getId()]);
                $winBuyer->setWins($response->getWinPrice());
                $buyers->save($winBuyer, true);

                $this->addFlash('success', "Auction added successfuly");
            
            if ($response->getStatus() == 'fail' )
                $this->addFlash('fail', "Auction added successfuly");

        }
        return $this->redirectToRoute('auction_view', ['auction' => $auction->getId()]);
    }

}
