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
        // https://www.tennis.com/players-rankings/
        $wta_images = array(
            'Barty' => 'https://photoresources.wtatennis.com/photo-resources/2020/09/30/d05d8377-816c-4499-bc48-5e307c893cc7/Barty_Hero-Smile.png?height=720',
            'Sabalenka' => 'https://photoresources.wtatennis.com/photo-resources/2020/09/30/077d0d47-22d6-4efc-b3a9-e13ff64b0934/Sabalenka_Hero-Smile.png?height=720',
            'Pliskova' => 'https://photoresources.wtatennis.com/photo-resources/2020/09/30/d2107552-9555-4c17-9eab-6361b940905e/Pliskova_Hero-Smile.png?height=720',
            'Krejcikova' => 'https://photoresources.wtatennis.com/photo-resources/2020/09/30/51357b9f-48f7-48ab-948a-f86e07e5dae6/Krejcikova_Hero-Smile.png?height=720',
            'Muguruza' => 'https://photoresources.wtatennis.com/photo-resources/2020/09/30/58137bc5-55f4-44aa-9260-e9c39d644c36/Muguruza_Hero-Smile.png?height=720',
            'Svitolina' => 'https://photoresources.wtatennis.com/photo-resources/2021/01/12/d3ab2f03-ce4c-483f-9174-97b29d0bed3d/Svitolina_Hero-Smile.png?height=720',
            'Sakkari' => 'https://photoresources.wtatennis.com/photo-resources/2020/07/02/64a1e097-a3fd-4f3a-8dbd-353a3317aa3d/Sakkari_Hero-Smile.png?height=720',
            'Jabeur' => 'https://photoresources.wtatennis.com/photo-resources/2019/10/08/cd7419cc-a9a6-4215-ab9a-3709a5d49391/VbhVIRWm.png?height=720',
            'Bencic' => 'https://photoresources.wtatennis.com/photo-resources/2020/09/30/88798690-26c2-4a72-9692-e24a2353479e/Bencic_Hero-Smile.png?height=720',
            'Osaka' => 'https://photoresources.wtatennis.com/photo-resources/2021/01/19/f5e01763-eee7-449f-999a-dcee1011c20e/Osaka_Hero-Smile.png?height=720',
            'Swiatek' => 'https://photoresources.wtatennis.com/photo-resources/2021/09/20/9cdb0ca8-91a7-4d23-90ac-9ee062d5e179/Swiatek_Hero-Smile.png?height=720',
            'Kerber' => 'https://photoresources.wtatennis.com/photo-resources/2019/10/08/8762852b-5a86-414d-87ee-2efad80d4e64/tfkMNfJw.png?height=720',
            'Badosa' => 'https://photoresources.wtatennis.com/photo-resources/2021/03/17/182f2065-6b4f-4d92-ac0a-68a857d1ce62/Badosa_Hero-Smile-v2.png?height=720',
            'Kenin' => 'https://photoresources.wtatennis.com/photo-resources/2020/09/30/8050465e-d3e8-4bd7-929b-453a57c50d70/Kenin_Hero-Smile.png?height=720',
            'Kvitova' => 'https://photoresources.wtatennis.com/photo-resources/2020/09/30/1dfdc52b-00e3-471e-8940-3fa228a32117/Kvitova_Hero-Smile.png?height=720',
            'Pavlyuchenkova' => 'https://photoresources.wtatennis.com/photo-resources/2020/09/30/8b7cc4ab-687e-4365-84b5-6b72be0bc56c/Pavlyuchenkova_Hero-Smile.png?height=720',
            'Rybakina' => 'https://photoresources.wtatennis.com/photo-resources/2020/07/02/14267b45-e3d1-4b43-bdda-fce6e82312c7/Rybakina_Hero-Smile.png?height=720',
            'Mertens' => 'https://photoresources.wtatennis.com/photo-resources/2020/09/30/74d2e168-fe5c-4a6a-83e9-b4c4d0876e4b/Mertens_Hero-Smile.png?height=720',
            'Halep' => 'https://photoresources.wtatennis.com/photo-resources/2019/10/08/62ecc4f9-a397-4c6f-9c8c-cea093c5ee9c/WzQOhOCP.png?height=720',
            'Kontaveit' => 'https://photoresources.wtatennis.com/photo-resources/2020/09/30/86ebc6fb-2b6c-4f22-bdab-632c75febac9/Kontaveit_Hero-Smile.png?height=720',
        );

        $atp_images = array(
            'Djokovic' => 'https://www.atptour.com/-/media/tennis/players/head-shot/2019/djokovic_head_ao19.png',
            'Korda' => 'https://www.atptour.com/en/search-results/-/media/tennis/players/head-shot/2021/08/22/18/00/korda_head_august_2021_final.png',
        );

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
                $explode = explode(',', $competitor['competitor']['name']);
                if($ranking['gender'] == 'men'){
                    if(isset($explode[0])){
                        $prarm['image'] = (isset($atp_images[$explode[0]])) ? $atp_images[$explode[0]] : 'https://www.atptour.com/-/media/tennis/players/head-shot/2020/' . strtolower($explode[0]). '_head_ao20.png';
                    }else{
                        $prarm['image'] = '';
                    }
                }else{
                    if(isset($explode[0]) && isset($wta_images[$explode[0]])){
                        $prarm['image'] = $wta_images[$explode[0]];
                    }else{
                        $prarm['image'] = '';
                    }
                }
                
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
