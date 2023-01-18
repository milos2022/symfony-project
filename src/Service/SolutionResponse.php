<?php 

namespace App\Service;

class SolutionResponse
{
    private ?string $status;
    private ?string $buyerName;
    private int $winPrice;
    private int $buyerPrice;

    public function __construct(string $status, ?string $buyerName, int $winPrice, int $buyerPrice)
    {
        $this->status = $status;
        $this->buyerName = $buyerName;
        $this->winPrice = $winPrice;
        $this->buyerPrice = $buyerPrice;
    }

    public function getStatus(): string 
    {
        return $this->status;
    }

    public function getBuyerName(): string 
    {
        return $this->buyerName;
    }

    public function getWinPrice(): string 
    {
        return $this->winPrice;
    }

    public function getBuyerPrice(): string 
    {
        return $this->buyerPrice;
    }



   

   
}