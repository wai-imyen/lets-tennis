<?php

namespace App\Services;

use LINE\LINEBot\MessageBuilder\TemplateMessageBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\ImageCarouselTemplateBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\ImageCarouselColumnTemplateBuilder;
use LINE\LINEBot\TemplateActionBuilder\UriTemplateActionBuilder;

class LineBotService
{

    /**
     * @param array $data
     * @param string $notificationText
     * @return TemplateMessageBuilder
     */
    public function buildTemplateMessageBuilder(array $data, string $notificationText = '新通知來囉!'): array
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
}