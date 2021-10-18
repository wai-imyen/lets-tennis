<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use LINE\LINEBot;
use LINE\LINEBot\Event\MessageEvent;
use LINE\LINEBot\Event\PostbackEvent;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use App\Services\LineBotService;
use App\Services\SportradarTennisService;
use App\Models\Competitor;
use App\Repositories\UserRepository;
use App\Repositories\CompetitorRepository;

class LineBotController extends Controller
{
    private $client;
    private $bot;
    private $channel_access_token;
    private $lineBotService;
    private $userRepository;

    public function __construct(LineBotService $lineBotService, SportradarTennisService $sportradarTennisService, UserRepository $userRepository, CompetitorRepository $competitorRepository)
    {
        $this->channel_access_token = env('LINE_BOT_CHANNEL_ACCESS_TOKEN');
        $this->channel_secret = env('LINE_BOT_CHANNEL_SECRET');
        $this->client = new CurlHTTPClient($this->channel_access_token);
        $this->bot = new LINEBot($this->client, ['channelSecret' => $this->channel_secret]);
        
        $this->lineBotService = $lineBotService;
        $this->sportradarTennisService = $sportradarTennisService;
        $this->userRepository = $userRepository;
        $this->competitorRepository = $competitorRepository;
    }

    /**
	 * LINE Callback
	 *
	 * @param	Request	$request
	 *
	 * return 
	 */
    public function webhook(Request $request)
    {
        $bot = $this->bot;
        $signature = $request->header(\LINE\LINEBot\Constant\HTTPHeader::LINE_SIGNATURE);
        $body = $request->getContent();

        try {
            $events = $bot->parseEventRequest($body, $signature);
            // 將收到的訊息加入LOG
            Log::info($events); 
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }

        foreach($events as $event){
            // 取得用戶 replyToken
            $replyToken = $event->getReplyToken();
            // 取得用戶 userId
            $userId = $event->getUserId();
            // 取得用戶資料
            $res = $bot->getProfile($userId);
            if ($res->isSucceeded()) {
                $profile = $res->getJSONDecodedBody();
                $displayName = $profile['displayName'];
            }else{
                $displayName = 'User';
            }
            
            // 建立用戶至資料庫
            $this->userRepository->addLineUser($displayName, $userId);

            // 接收訊息事件
            if ($event instanceof MessageEvent){
                // 取得訊息類型
                $messageType = $event->getMessageType();
                // 取得文字內容
                $text = $event->getText();
                switch ($messageType){
                    case 'text':
                        // 回覆模板訊息
                        if(strtoupper($text) == 'LIKE'){
                            // 取得收藏清單
                            $wishlist = $this->userRepository->getWishlist($userId);
                            if($wishlist){
                                // 組織輪播訊息內容
                                foreach($wishlist as $competior){
                                    $imagePath = ($competior->image) ? $competior->image : 'https://apt.co.zw/wp-content/uploads/2016/12/apt-avatar.jpg';
                                    $data[] = array(
                                        'title' => $competior->name,
                                        'text' => 'No.' . $competior->rank,
                                        'imagePath' => $imagePath,
                                        'options' => array(
                                            array(
                                                'label' => '近期賽事',
                                                'data' => 'action=summary&urnCompetitor=' . $competior->urn_competitor,
                                            ),
                                            array(
                                                'label' => '取消收藏',
                                                'data' => 'action=unlike&urnCompetitor=' . $competior->urn_competitor,
                                            ),
                                        ),
                                    );
                                }
                                $targets = $this->lineBotService->buildCarouselTemplateMessageBuilder($data);
                                foreach($targets as $target){
                                    // 發送圖文輪播訊息
                                    $bot->replyMessage($replyToken, $target);
                                }
                            }else{
                                $bot->replyText($replyToken, '收藏清單尚無資料！');
                            }
                            
                        } elseif(strtoupper($text) == 'WTA' || strtoupper($text) == 'ATP'){
                            // 回應排名查詢快速訊息
                            $this->lineBotService->rankQucikReply($replyToken, strtoupper($text));

                        }elseif(strpos(strtoupper($text), 'WTA') !== false || strpos(strtoupper($text), 'ATP') !== false){
                            // 解析取得的訊息內容
                            $explode = explode('-', $text);
                            $gender = (isset($explode[0]) && strtoupper($explode[0]) == 'WTA') ? 'women' : 'men';
                            $rank = (isset($explode[1])) ? (int)$explode[1] : 1;
                            // 取得選手列表
                            $competiors = $this->competitorRepository->getCompetitors($filter = array('gender' => $gender), ($rank - 1));
                            // 組織輪播訊息內容
                            if($competiors){
                                foreach($competiors as $competior){
                                    $imagePath = ($competior['image']) ? $competior['image'] : 'https://apt.co.zw/wp-content/uploads/2016/12/apt-avatar.jpg';
                                    $data[] = array(
                                        'title' => $competior['name'],
                                        'text' => 'No.' . $competior['rank'],
                                        'imagePath' => $imagePath,
                                        'options' => array(
                                            array(
                                                'label' => '近期賽事',
                                                'data' => 'action=summary&urnCompetitor=' . $competior['urn_competitor'],
                                            ),
                                            array(
                                                'label' => '加入收藏',
                                                'data' => 'action=like&urnCompetitor=' . $competior['urn_competitor'],
                                            ),
                                        ),
                                    );
                                }
                                $targets = $this->lineBotService->buildCarouselTemplateMessageBuilder($data);
                                foreach($targets as $target){
                                    // 發送圖文輪播訊息
                                    $bot->replyMessage($replyToken, $target);
                                }
                            }else{
                                $bot->replyText($replyToken, '暫無選手資料！');
                            }
                        }else{
                            // 回覆用戶文字訊息
                            $bot->replyText($replyToken, 'Let\'s Tennis!');
                        }
                        break;

                    default:
                        break;
                }
            } 
            
            // 接收回傳事件
            if ($event instanceof PostbackEvent) {
                
                // 解析取得回傳參數資料
                $postbackData = [];
                parse_str($event->getPostbackData(), $postbackData);
                
                // 辨別動作參數執行
                switch ($postbackData['action']) {
                    case 'like':
                        // 取得選手編號
                        $urnCompetitor = (isset($postbackData['urnCompetitor'])) ? $postbackData['urnCompetitor'] : '';
                        $res = $this->userRepository->addWishlist($userId, $urnCompetitor);
                        if($res){
                            $bot->replyText($replyToken, '成功加入收藏！');
                        }else{
                            $bot->replyText($replyToken, '操作失敗！');
                        }
                        break;
                    case 'unlike':
                        // 取得選手編號
                        $urnCompetitor = (isset($postbackData['urnCompetitor'])) ? $postbackData['urnCompetitor'] : '';
                        $res = $this->userRepository->removeWishlist($userId, $urnCompetitor);
                        if($res){
                            $bot->replyText($replyToken, '成功取消收藏！');
                        }else{
                            $bot->replyText($replyToken, '操作失敗！');
                        }
                        break;
                    case 'summary':
                        // 取得選手編號
                        $urnCompetitor = (isset($postbackData['urnCompetitor'])) ? $postbackData['urnCompetitor'] : '';
                        // 取得選手基本資料
                        $competior = $this->competitorRepository->getCompetitorByUrn($urnCompetitor);
                        if($competior){
                            // 取得該選手近期賽事資料
                            $results = $this->sportradarTennisService->getCompetitorSummaries($urnCompetitor);
                            // 組織訊息內容
                            $message = $competior->name . PHP_EOL;
                            $message .= '------------------------------'. PHP_EOL;
                            foreach($results as $result){
                                $message .= '時間：'. date('Y-m-d H:i:s', strtotime($result['start_time'])) . PHP_EOL;
                                $message .= '賽事：'. $result['event'] . PHP_EOL;
                                $message .= '選手：'. $result['competitors'][0]['name'] . ' V.S ' . $result['competitors'][1]['name'] . PHP_EOL;
                                $message .= '比分：'. $result['competitors'][0]['score'] . ' : ' . $result['competitors'][1]['score'] . PHP_EOL;
                                if($result['result']){
                                    $message .= '結果：'. $result['result'] . PHP_EOL;
                                }
                                $message .= '------------------------------'. PHP_EOL;
                            }
                            // 回傳訊息
                            $bot->replyText($replyToken, $message);
                        }
                        break;
                    default:
                        break;
                }
            }
        }
    }

    public function test(Request $request)
    {
        
    }
}
