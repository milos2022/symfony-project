<?php 

namespace App\Service;

use App\Entity\Buyer;
use App\Entity\Auction;
use App\Repository\BuyerRepository;
use App\Repository\AuctionRepository;
use DateTimeImmutable;

class Algorithm
{
    private ?Object $jsondata = null;

    public function __construct(private string $targetDirectory, private AuctionRepository $auctions, private BuyerRepository $buyers )
    {
        $this->targetDirectory = $targetDirectory;
        $this->auctions = $auctions;
        $this->buyers = $buyers;
    }

    public function run(Auction $auction): SolutionResponse 
    {
        if(is_null($this->jsondata))
        $this->loadJsonFromFile($auction->getImportFilename());

        $bids = $this->getBiggestBidsFromBuyers($this->jsondata, $auction);
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
        $secondPrice = array_shift($bids) ?? $auction->getReservePrice();

        return new SolutionResponse('success', $winner, $secondPrice, $winnerPrice);
    }

    private function loadJsonFromFile(string $filename) : bool
    {
        try{
            $this->jsondata = json_decode(file_get_contents($this->targetDirectory . "/" . $filename));
            return true;
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

            $bids[$key] = intval(max($val));
        }
        arsort($bids);

        return $bids;
    }

    public function getJsondata(): ?Object
    {
        return $this->jsondata;
    }

    public function setJsondata(Object $jsondata): self
    {
        $this->jsondata = $jsondata;

        return $this;
    }
}