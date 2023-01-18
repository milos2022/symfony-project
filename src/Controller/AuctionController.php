<?php

namespace App\Controller;

use App\Entity\Buyer;
use DateTimeImmutable;
use App\Entity\Auction;
use App\Form\AuctionType;
use App\Service\FileUploader;
use App\Form\Type\ImportFileType;
use PhpParser\Node\Stmt\TryCatch;
use App\Repository\BuyerRepository;
use App\Repository\AuctionRepository;
use App\Service\Algorithm;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
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
    public function run(Auction $auction, Algorithm $algorithm): RedirectResponse
    {
        //if(is_null($auction->getClosedAt()) )
        //{
            $response = $algorithm->run($auction); 
            if ($response->getStatus() == 'success' )
                $this->addFlash('success', "Auction added successfuly");
            
            if ($response->getStatus() == 'fail' )
                $this->addFlash('fail', "Auction added successfuly");

       // }
        return $this->redirectToRoute('auction_view', ['auction' => $auction->getId()]);
    }











    #[Route('/auction/import', name: 'auction_import')]
    public function import(AuctionRepository $auctions, BuyerRepository $buyers): Response
    {
        $jsonData = $this->loadJsonFromFile("data.json");
        
        $auction = new Auction;
        $auction->setReservePrice($jsonData->reservePrice);
        $auction->setName("Auction" . $jsonData->reservePrice);
        $auction->setCreatedAt(new DateTimeImmutable());
        
        $bids = $this->getBiggestBidsFromBuyers($jsonData, $auction);

        $auctions->save($auction, true);

        if(count($bids) === 0) 
        {
            echo "No valid bids!";
            exit;
        } 
        
        $winner = array_key_first($bids);

        // remove biggest price
        $bids = array_diff($bids, [array_values($bids)[0]]);

        // if there is only one Buyer second price is sealed price
        $secondPrice = array_shift($bids) ?? $jsonData->reservePrice;
    
        $winBuyer = $buyers->findOneBy(["name" => $winner]);
        $winBuyer->setWins($secondPrice);
        $buyers->save($winBuyer, true);

        return $this->render(
            'auction/import.html.twig',
            [
                'auction' => $auction,
                'winner' => $winner,
                'winnerPrice' => $secondPrice
            ]
        );
    }

    private function loadJsonFromFile(string $filename) : Object
    {
        try{
            return json_decode(file_get_contents(getcwd()."/".$filename));
        }
        catch(\Exception $e){
            echo "Cant open file";
            exit;
        } 
    }

    private function getBiggestBidsFromBuyers(Object $data, Auction $auction) : Array 
    {
        $bids = array();
        
        foreach($data->Buyers as $key => $val){
            $buyer = new Buyer();
            $buyer->setName($key);
            $buyer->setWins(0);
            $buyer->setAuction($auction);

            $auction->addBuyer($buyer);
        
            // ignore buyers with zero bids
            if(count($val) == 0) continue;

            // Biggest bid from Buyer
            $max = max($val);

            // ignore all bids bellow sealed price
            if($max < $data->reservePrice) continue;
                
            $bids[$key] = max($val);
        }
        arsort($bids);

        return $bids;
    }



}
