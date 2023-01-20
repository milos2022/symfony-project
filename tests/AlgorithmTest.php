<?php

namespace App\Tests;

use stdClass;
use App\Entity\Auction;
use App\Service\Algorithm;
use App\Repository\BuyerRepository;
use App\Repository\AuctionRepository;
use App\Service\SolutionResponse;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class AlgorithmTest extends KernelTestCase
{
    private $container;
    private $algorithm;
    public function setUp(): void
    {
        self::bootKernel();
        $this->container = static::getContainer();

        $this->algorithm = $this->container->get(Algorithm::class);
         
        $auctionReposytoryMock = $this->createMock(AuctionRepository::class);
        $buyerRepositoryMock = $this->createMock(BuyerRepository::class);

        $this->algorithm = new Algorithm(
            'a',
            $auctionReposytoryMock,
            $buyerRepositoryMock  
        );
    }

    /**
     * @dataProvider dataForRun
     */
    public function testRun($jsonDataObject, $name, $sealedPrice, $results): void
    {        
        $auction = new Auction();
        $auction->setName($name);
        $auction->setReservePrice($sealedPrice);
        $auction->setImportFilename('test.json');

        $this->algorithm->setJsondata($jsonDataObject);
        $response = $this->algorithm->run($auction);

        $this->assertInstanceOf(SolutionResponse::class, $response);
        $this->assertEquals($results, $response);
    }

    /**
     * @dataProvider dataGetBiggestBidFromBuyers
     */
    public function testGetBiggestBidFromBuyers($jsonDataObject, $results) : void
    {
        $response = $this->algorithm->getBiggestBidsFromBuyers($jsonDataObject, new Auction());

        $this->assertIsArray($response);
        $this->assertEquals($results, $response);
    }

    public function dataForRun()
    {
        $object = new stdClass();
        $object->Buyers = new stdClass();
        $object->Buyers->A = [110, 130];
        $object->Buyers->B = [];
        $object->Buyers->C = [95];
        $object->Buyers->D = [105, 115, 140];
        $object->Buyers->F = [132, 135, 140];

        $response = new SolutionResponse('success', 'D', 130, 140);

        yield[
            $object, "Testing name", 120, $response
        ];

        /* ---------------------------------------------- */
        
        $object = new stdClass();
        $object->Buyers = new stdClass();
        $object->Buyers->A = [110];
        $object->Buyers->B = [];
        $object->Buyers->C = [110];
        $object->Buyers->D = [];
        $object->Buyers->F = [];

        $response = new SolutionResponse('success', 'A', 120, 110);

        yield[
            $object, "Testing name", 120, $response
        ];

        /* ---------------------------------------------- */
        
        $object = new stdClass();
        $object->Buyers = new stdClass();
        $object->Buyers->A = [];
        $object->Buyers->B = [];
        $object->Buyers->C = [];
        $object->Buyers->D = [];
        $object->Buyers->F = [];

        $response = new SolutionResponse('fail', null, 0, 0);

        yield[
            $object, "Testing name", 120, $response
        ];
    }

    public function dataGetBiggestBidFromBuyers() 
    {
        $object = new stdClass();
        $object->Buyers = new stdClass();
        $object->Buyers->A = [110, 130];
        $object->Buyers->B = [];
        $object->Buyers->C = [95];
        $object->Buyers->D = [105, 115, 140];
        $object->Buyers->F = [132, 135, 140];

        $response = ['A' => 130,
                    'C' => 95,
                    'D' => 140,
                    'F' => 140
                ];

        yield[
            $object, $response
        ];

        /* ---------------------------------------------- */

        $object = new stdClass();
        $object->Buyers = new stdClass();
        $object->Buyers->A = [];
        $object->Buyers->B = [15];
        $object->Buyers->C = [];
        $object->Buyers->D = [];
        $object->Buyers->F = [];

        $response = ['B' => 15
                ];

        yield[
            $object, $response
        ];

        /* ---------------------------------------------- */

        $object = new stdClass();
        $object->Buyers = new stdClass();
        $object->Buyers->A = [];
        $object->Buyers->B = ['asd'];
        $object->Buyers->C = [];
        $object->Buyers->D = [150];
        $object->Buyers->F = [130];

        $response = ['B' => 0,
                    'D' => 150,
                    'F' => 130
                ];

        yield[
            $object, $response
        ];
    }
}
