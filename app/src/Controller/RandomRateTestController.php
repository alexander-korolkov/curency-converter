<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RandomRateTestController extends AbstractController
{
    #[Route('/api/v1/test-provider', name: 'random_rate', methods: ['GET'])]
    public function randomRate(Request $request): Response
    {
        $get = $request->query->get('get');
        if ($get === 'currency_list') {
            $response = [
                'status' => 200,
                'message' => 'rates',
                'data' => ['BCHEUR','BCHGBP','BCHJPY','BCHRUB','BCHUSD','BCHXRP','BTCBCH','BTCEUR','BTCGBP','BTCJPY','BTCRUB','BTCUSD','BTCXRP','BTGUSD','BYNRUB','CADRUB','CHFRUB','CNYEUR','CNYRUB','CNYUSD','ETHEUR','ETHGBP','ETHJPY','ETHRUB','ETHUSD','EURAED','EURAMD','EURBGN','EURBYN','EURGBP','EURJPY','EURKZT','EURRUB','EURTRY','EURUSD','GBPAUD','GBPBYN','GBPJPY','GBPRUB','GELRUB','GELUSD','IDRUSD','JPYAMD','JPYAZN','JPYRUB','LKREUR','LKRRUB','LKRUSD','MDLEUR','MDLRUB','MDLUSD','MMKEUR','MMKRUB','MMKUSD','RSDEUR','RSDRUB','RSDUSD','RUBAED','RUBAMD','RUBAUD','RUBBGN','RUBKZT','RUBMYR','RUBNZD','RUBSGD','RUBTRY','RUBUAH','THBCNY','THBEUR','THBRUB','USDAED','USDAMD','USDAUD','USDBGN','USDBYN','USDCAD','USDGBP','USDILS','USDJPY','USDKGS','USDKZT','USDMYR','USDRUB','USDTHB','USDUAH','USDVND','XRPEUR','XRPGBP','XRPJPY','XRPRUB','XRPUSD','ZECUSD']
            ];

            return $this->json($response);
        }

        $pairs = $request->query->get('pairs');
        $pairs = explode(',', $pairs);
        $data = [];
        foreach ($pairs as $pair) {
            // Generate random rate
            $data[$pair] = rand(0.1, 100) + rand(0, 100) / 100;
        }

        $response = [
            'status' => 200,
            'message' => 'rates',
            'data' => $data
        ];

        return $this->json($response);
    }
}
