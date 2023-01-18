<?php 

namespace App\Service;

use App\Entity\Buyer;
use App\Entity\Auction;
use App\Repository\BuyerRepository;
use App\Repository\AuctionRepository;
use DateTimeImmutable;

class Algorithm
{
    public function __construct(private string $targetDirectory, private AuctionRepository $auctions, private BuyerRepository $buyers )
    {
        $this->targetDirectory = $targetDirectory;
        $this->auctions = $auctions;
        $this->buyers = $buyers;
    }

    public function run(Auction $auction): SolutionResponse 
    {
        $jsonData = $this->loadJsonFromFile($auction->getImportFilename());

        $bids = $this->getBiggestBidsFromBuyers($jsonData, $auction);
        $auction->setClosedAt(new DateTimeImmutable());
        $this->auctions->save($auction, true);

        if(count($bids) === 0) 
        {
            return new SolutionResponse('fail', null, 0, 0);
        } 
        
        $winner = array_key_first($bids);
        $winnerPrice = array_values($bids)[0];

        // remove biggest price
        $bids = array_diff($bids, [array_values($bids)[0]]);

        // if there is only one Buyer second price is sealed price
        $secondPrice = array_shift($bids) ?? $jsonData->reservePrice;
    
        $winBuyer = $this->buyers->findOneBy(["name" => $winner, "Auction" => $auction->getId()]);
        $winBuyer->setWins($secondPrice);
        $this->buyers->save($winBuyer, true);

        return new SolutionResponse('success', $winner, $secondPrice, $winnerPrice);
    }

    private function loadJsonFromFile(string $filename) : Object
    {
        try{
            return json_decode(file_get_contents($this->targetDirectory . "/" . $filename));
        }
        catch(\Exception $e){
            echo "Cant open file";
            exit;
        } 
    }

    public function getBiggestBidsFromBuyers(Object $data, Auction $auction) : Array 
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
            if($max < $auction->getReservePrice()) continue;
                
            $bids[$key] = max($val);
        }
        arsort($bids);

        return $bids;
    }
}