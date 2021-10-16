<?php

namespace App\Services;

class SportradarTennisService {
    
    private $base_url;
    private $locale;
	private $api_key;

    /**
     * SportradarTennisAPI constructor.
     *
     */
    public function __construct()
    {
        $this->base_url = config('sportradar.base_url');
        $this->locale = config('sportradar.locale');
        $this->api_key = config('sportradar.api_key');
    }
    /**
     * 取得選手排名
     * 
     * @return array|null
     */
    public function getRankings()
    {
        $url = $this->base_url . '/' . $this->locale . '/rankings.json?api_key=' . $this->api_key;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if($httpCode == 200){
			return json_decode($response, true);
		}
        
        return null;
    }

    /**
     * 取得選手資料
     * 
     * @param string $urn_competitor
     * 
     * @return array|null
     */
    public function getCompetitorProfile(string $urn_competitor)
    {
        $url = $this->base_url . '/' . $this->locale . '/competitors/' . $urn_competitor . '/profile.json?api_key=' . $this->api_key;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if($httpCode == 200){
			return json_decode($response, true);
		}
        
        return null;
    }

    /**
     * 取得選手近期賽事
     * 
     * @param string $urn_competitor
     * 
     * @return array|null
     */
    public function getCompetitorSummaries(string $urn_competitor)
    {
        $url = $this->base_url . '/' . $this->locale . '/competitors/' . $urn_competitor . '/summaries.json?api_key=' . $this->api_key;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if($httpCode == 200){
			$res = json_decode($response, true);
            $summaries = array();
            for($i=0; $i<=5; $i++){
                $summary = $res['summaries'][$i];
                $data = array();
                // 時間
                $data['start_time'] = $summary['sport_event']['start_time'];
                // 賽事名稱
                $data['event'] = $summary['sport_event']['sport_event_context']['season']['name'];
                // 比賽狀態
                $data['match_status'] = $summary['sport_event_status']['match_status']; // ended, walkover
                $data['competitors'] = array();
                foreach($summary['sport_event']['competitors'] as $competitor){
                    $competitors = array(
                        'id' => $competitor['id'],
                        'name' => $competitor['name'],
                    );
                    // 主客比分
                    if($competitor['qualifier'] == 'home' && isset($summary['sport_event_status']['home_score'])){
                        $competitors['score'] = $summary['sport_event_status']['home_score'];
                    }else if($competitor['qualifier'] == 'away' && isset($summary['sport_event_status']['away_score'])){
                        $competitors['score'] = $summary['sport_event_status']['away_score'];
                    }else{
                        $competitors['score'] = 0;
                    }
                    
                    $data['competitors'][] = $competitors;
                }
                // 比賽狀態
                $data['match_status'] = $summary['sport_event_status']['match_status'];

                // 是否有勝者
                $data['winner_id'] = (isset($summary['sport_event_status']['winner_id'])) ? $summary['sport_event_status']['winner_id'] : '';
                // 是否有結果
                if(in_array($summary['sport_event_status']['match_status'], array('ended', 'walkover'))){
                    if($data['winner_id'] == $urn_competitor){
                        $data['result'] = 'win';
                    }else{
                        $data['result'] = 'lose';
                    }
                }else{
                    $data['result'] = '';
                }
                // 儲存賽果
                $summaries[] = $data;
            }

            return $summaries;
		}
        
        return null;
    }
}