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

class LineBotController extends Controller
{
    private $client;
    private $bot;
    private $channel_access_token;
    private $lineBotService;

    public function __construct(LineBotService $lineBotService, SportradarTennisService $sportradarTennisService)
    {
        $this->channel_access_token = env('LINE_BOT_CHANNEL_ACCESS_TOKEN');
        $this->channel_secret = env('LINE_BOT_CHANNEL_SECRET');
        $this->client = new CurlHTTPClient($this->channel_access_token);
        $this->bot = new LINEBot($this->client, ['channelSecret' => $this->channel_secret]);
        $this->lineBotService = $lineBotService;
        $this->sportradarTennisService = $sportradarTennisService;
    }

    /**
	 * LINE Callback
	 *
	 * @param	Request	$request
	 *
	 * return 
	 */
    public function webhook(Request $request, LineBotService $lineBotService)
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
                            $data[] = array(
                                'imagePath' => 'https://photoresources.wtatennis.com/photo-resources/2019/10/08/62ecc4f9-a397-4c6f-9c8c-cea093c5ee9c/WzQOhOCP.png?height=720',
                                'directUri' => 'https://photoresources.wtatennis.com/photo-resources/2019/10/08/62ecc4f9-a397-4c6f-9c8c-cea093c5ee9c/WzQOhOCP.png?height=720',
                                'label' => 'Halep'
                            );
                            $data[] = array(
                                'imagePath' => 'https://photoresources.wtatennis.com/photo-resources/2019/10/08/8762852b-5a86-414d-87ee-2efad80d4e64/tfkMNfJw.png?height=720',
                                'directUri' => 'https://photoresources.wtatennis.com/photo-resources/2019/10/08/8762852b-5a86-414d-87ee-2efad80d4e64/tfkMNfJw.png?height=720',
                                'label' => 'Kerber'
                            );
                            $data[] = array(
                                'imagePath' => 'https://photoresources.wtatennis.com/photo-resources/2021/01/19/f5e01763-eee7-449f-999a-dcee1011c20e/Osaka_Hero-Smile.png?height=720',
                                'directUri' => 'https://photoresources.wtatennis.com/photo-resources/2021/01/19/f5e01763-eee7-449f-999a-dcee1011c20e/Osaka_Hero-Smile.png?height=720',
                                'label' => 'Osaka'
                            );
                            $targets = $this->lineBotService->buildImageCarouselColumnTemplateMessageBuilder($data);
                            
                            foreach($targets as $target){
                                $bot->replyMessage($replyToken, $target);
                            }
                        } elseif(strtoupper($text) == 'WTA' || strtoupper($text) == 'ATP'){
                            $type = (strtoupper($text) == 'WTA') ? 'women' : 'men';
                            $competiors = Competitor::query()->where('gender','=', $type)->orderBy('rank', 'asc')->limit(10)->get();
                            foreach($competiors as $competior){
                                $imagePath = ($competior['image']) ? $competior['image'] : 'https://apt.co.zw/wp-content/uploads/2016/12/apt-avatar.jpg';
                                $data[] = array(
                                    'title' => $competior['name'],
                                    'text' => 'No.'.$competior['rank'],
                                    'imagePath' => $imagePath,
                                    'options' => array(
                                        array(
                                            'label' => '近期賽事',
                                            'data' => 'action=summary&competitorId=' . $competior['urn_competitor'],
                                        ),
                                        array(
                                            'label' => '加入收藏',
                                            'data' => 'action=like&competitorId=' . $competior['urn_competitor'],
                                        ),
                                    ),
                                );
                            }
                            $targets = $this->lineBotService->buildCarouselTemplateMessageBuilder($data);
                            
                            foreach($targets as $target){
                                $bot->replyMessage($replyToken, $target);
                            }
                        }else{
                            // 回覆用戶文字訊息
                            $bot->replyText($replyToken, 'Hello world!');
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
                    case 'info':
                        // call api ..
                        break;
                    case 'like':
                        
                        break;
                    case 'summary':
                        // 取得查詢ID
                        $competitorId = (isset($postbackData['competitorId'])) ? $postbackData['competitorId'] : '';
                        $competior = Competitor::query()->where('urn_competitor','=', $competitorId)->first('name');
                        if($competior){
                            // 取得該選手近期賽事資料
                            $results = $this->sportradarTennisService->getCompetitorSummaries($competitorId);
                            
                            // 訊息內容
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
                            $bot->replyText($replyToken, $message);
                        }
                        break;
                    default:
                        break;
                }

                // $bot->replyText($replyToken, $postbackData);
            }
        }
    }

    public function test(Request $request, LineBotService $lineBotService)
    {
        
    }
}
