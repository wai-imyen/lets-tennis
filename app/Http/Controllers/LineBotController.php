<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use LINE\LINEBot;
use LINE\LINEBot\Event\MessageEvent;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use App\Services\LineBotService;

class LineBotController extends Controller
{
    private $client;
    private $bot;
    private $channel_access_token;
    private $lineBotService;

    public function __construct(LineBotService $lineBotService)
    {
        $this->channel_access_token = env('LINE_BOT_CHANNEL_ACCESS_TOKEN');
        $this->channel_secret = env('LINE_BOT_CHANNEL_SECRET');
        $this->client = new CurlHTTPClient($this->channel_access_token);
        $this->bot = new LINEBot($this->client, ['channelSecret' => $this->channel_secret]);
        $this->lineBotService = $lineBotService;
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
            if ($event instanceof MessageEvent){
                // 取得訊息類型
                $message_type = $event->getMessageType();
                // 取得文字內容
                $text = $event->getText();
                switch ($message_type){
                    case 'text':
                        // 回覆模板訊息
                        if($text == '女子選手排名'){
                            $data[] = array(
                                'imagePath' => 'https://photoresources.wtatennis.com/photo-resources/2019/10/08/62ecc4f9-a397-4c6f-9c8c-cea093c5ee9c/WzQOhOCP.png?height=200',
                                'directUri' => 'https://photoresources.wtatennis.com/photo-resources/2019/10/08/62ecc4f9-a397-4c6f-9c8c-cea093c5ee9c/WzQOhOCP.png?height=200',
                                'label' => 'Halep'
                            );
                            $data[] = array(
                                'imagePath' => 'https://photoresources.wtatennis.com/photo-resources/2019/10/08/8762852b-5a86-414d-87ee-2efad80d4e64/tfkMNfJw.png?height=200',
                                'directUri' => 'https://photoresources.wtatennis.com/photo-resources/2019/10/08/8762852b-5a86-414d-87ee-2efad80d4e64/tfkMNfJw.png?height=200',
                                'label' => 'Kerber'
                            );
                            $data[] = array(
                                'imagePath' => 'https://photoresources.wtatennis.com/photo-resources/2021/01/19/f5e01763-eee7-449f-999a-dcee1011c20e/Osaka_Hero-Smile.png?height=200',
                                'directUri' => 'https://photoresources.wtatennis.com/photo-resources/2021/01/19/f5e01763-eee7-449f-999a-dcee1011c20e/Osaka_Hero-Smile.png?height=200',
                                'label' => 'Osaka'
                            );
                            $targets = $this->lineBotService->buildTemplateMessageBuilder($data);
                            
                            foreach($targets as $target){
                                $bot->replyMessage($replyToken, $target);
                            }
                        } else{
                            // 回覆用戶文字訊息
                            $bot->replyText($replyToken, 'Hello world!');
                        }
                    break;
                }
            }
        }
    }
}
