<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SportradarTennisService;
use App\Models\Competitor;

class SportradarTennisController extends Controller
{
    private $sportradarTennisService;
    
    public function __construct(SportradarTennisService $sportradarTennisService)
    {
        $this->sportradarTennisService = $sportradarTennisService;
    }

    public function updateCompetitor()
    {
        $rankings = $this->sportradarTennisService->getRankings();
        foreach($rankings['rankings'] as $ranking){
            foreach($ranking['competitor_rankings'] as $competitor){
                $prarm = [];
                $prarm['gender'] = $ranking['gender'];
                $prarm['rank'] = $competitor['rank'];
                $prarm['urn_competitor'] = $competitor['competitor']['id'];
                $prarm['name'] = $competitor['competitor']['name'];
                $prarm['name_zht'] = $competitor['competitor']['name'];
                $prarm['country'] = $competitor['competitor']['country'];
                
                $query = Competitor::query()->where('urn_competitor','=',$prarm['urn_competitor']);

                if( ! $res = $query->first()){
                    Competitor::create($prarm);
                }else{

                    if(config('sportradar.locale') == 'zht'){
                        unset($prarm['name']);
                    }else{
                        unset($prarm['name_zht']);
                    }
                    $query->update($prarm);
                }
            }
        }
    }
}
