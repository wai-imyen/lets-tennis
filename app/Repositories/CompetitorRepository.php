<?php
namespace App\Repositories;

use App\Models\Competitor;

class CompetitorRepository
{

    /**
     * 以編號取得選手資料
     * 
     * @param string $urn_competitor
     * 
     * @return object|null
     */
    public function getCompetitorByUrn(string $urn_competitor)
    {
        return $competior = Competitor::query()
        ->where('urn_competitor','=', $urn_competitor)
        ->first();
    }

    /**
     * 取得選手列表
     * 
     * @param array $filter
     * @param integer $limit
     * 
     * @return object|null
     */
    public function getCompetitors(array $filter, $start = 0, $limit = 10)
    {
        $query = Competitor::query();

        if(isset($filter['gender'])){
            $query = $query->where('gender','=', $filter['gender']);
        }
        $query = $query->orderBy('rank', 'asc');
        $query = $query->skip($start);
        $query = $query->limit($limit);

        return $query->get();
    }
}