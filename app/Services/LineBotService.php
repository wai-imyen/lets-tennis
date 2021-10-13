<?php

namespace App\Services;

use LINE\LINEBot\MessageBuilder\TemplateMessageBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\ImageCarouselTemplateBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\ImageCarouselColumnTemplateBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselTemplateBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselColumnTemplateBuilder;
use LINE\LINEBot\TemplateActionBuilder\UriTemplateActionBuilder;
use LINE\LINEBot\TemplateActionBuilder\PostbackTemplateActionBuilder;

class LineBotService
{

    /**
     * 取得圖片輪播模板訊息資料
     * 
     * @param array $data
     * @param string $notificationText
     * @return TemplateMessageBuilder
     */
    public function buildImageCarouselColumnTemplateMessageBuilder(array $data, string $notificationText = '新通知來囉!'): array
    {
        $imageCarouselColumnTemplateBuilders = array_map(function ($d) {
            return $this->buildImageCarouselColumnTemplateBuilder(
                $d['imagePath'],
                $d['directUri'],
                $d['label']
            );
        }, $data);

        $tempChunk = array_chunk($imageCarouselColumnTemplateBuilders, 5);
        return array_map(function ($data) use ($notificationText) {
            return new TemplateMessageBuilder(
                $notificationText,
                new ImageCarouselTemplateBuilder($data)
            );
        }, $tempChunk);
    }

    /**
     * 建立圖片輪播模板
     * 
     * @param string $imagePath
     * @param string $directUri
     * @param string $label
     * @return ImageCarouselColumnTemplateBuilder
     */
    protected function buildImageCarouselColumnTemplateBuilder(
        string $imagePath,
        string $directUri,
        string $label
    ): ImageCarouselColumnTemplateBuilder {
        return new ImageCarouselColumnTemplateBuilder(
            $imagePath,
            new UriTemplateActionBuilder($label, $directUri)
        );
    }

    /**
     * 取得輪播模板訊息資料
     * 
     * @param array $data
     * @param string $notificationText
     * @return TemplateMessageBuilder
     */
    public function buildCarouselTemplateMessageBuilder(array $data, string $notificationText = '新通知來囉!'): array
    {
        $CarouselColumnTemplateBuilders = array_map(function ($d) {
            return $this->buildCarouselColumnTemplateBuilder(
                $d['title'],
                $d['text'],
                $d['imagePath'],
                $d['options'],
            );
        }, $data);

        $tempChunk = array_chunk($CarouselColumnTemplateBuilders, 5);
        return array_map(function ($data) use ($notificationText) {
            return new TemplateMessageBuilder(
                $notificationText,
                new CarouselTemplateBuilder($data)
            );
        }, $tempChunk);
    }

    /**
     * 建立輪播模板
     * 
     * @param string $title
     * @param string $text
     * @param string $imagePath
     * @param string $label
     * @param string $data
     * @return CarouselColumnTemplateBuilder
     */
    protected function buildCarouselColumnTemplateBuilder(
        string $title,
        string $text,
        string $imagePath,
        array $options
    ): CarouselColumnTemplateBuilder {
        
        $actionBuilders = [];
        foreach($options as $opt){
            $actionBuilders[] = new PostbackTemplateActionBuilder($opt['label'], $opt['data']);
        }

        return new CarouselColumnTemplateBuilder(
            $title,
            $text,
            $imagePath,
            $actionBuilders
        );
    }
}