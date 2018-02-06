<?php

namespace common\extensions\feedBuilder\formats;


use common\models\City;
use common\models\Experience;
use common\models\Price;

/**
 * Class FacebookProductXmlFormat
 * Format documentation - https://developers.facebook.com/docs/marketing-api/dynamic-product-ads/product-catalog/v2.9#feed-format
 * Online debug - https://business.facebook.com/ads/product_feed/debug
 * @package common\extensions\feedBuilder\formats
 */
class FacebookProductXmlFormat implements FormatInterface
{
    public $city;
    public $urlAffix = '';
    public $titlePrefix = '';

    /**
     * @var \Closure (array $attributes, Experience $experience)
     */
    public $itemHandler;

    public function __construct(City $city)
    {
        $this->city = $city;
    }

    /**
     * @return string
     */
    public function getHeader()
    {
        return '<?xml version="1.0"?>' . PHP_EOL
        . '<feed xmlns="http://www.w3.org/2005/Atom" xmlns:g="http://base.google.com/ns/1.0">' . PHP_EOL
        . '   <title>Test Store</title>' . PHP_EOL
        . '   <link rel="self" href="http://www.example.com"/>';
    }

    /**
     * @return string
     */
    public function getFooter()
    {
        return '</feed>';
    }

    /**
     * @param Experience $experience
     * @return string
     */
    public function getItem(Experience $experience)
    {
        $attributes = [];

        if ($experience->city) {

            //Название в каталоге
            $name = $this->titlePrefix . $experience->title;

            $experienceUrl = $experience->getUrl(true);
            $attributes    = [
                'id'                       => $experience->id,
                'link'                     => htmlspecialchars($experienceUrl . $this->urlAffix, ENT_QUOTES | ENT_XML1),
                'price'                    => $experience->price . ' ' . Price::getCurrency(),
                'image_link'               => $experience->getFilterThumbnailBig()->getSrc(true),
                'title'                    => '<![CDATA[' . html_entity_decode(strip_tags($name)) . ']]>',
                'short_description'        => '<![CDATA[' . html_entity_decode(strip_tags($experience->brief)) . ']]>',
                'description'              => '<![CDATA[' . html_entity_decode(strip_tags($experience->description)) . ']]>',
                'availability'             => $experience->isActive() ? 'in stock' : 'out of stock',
                'condition'                => 'new',
                'brand'                    => 'BODO',
                'google_product_category ' => 'Подарочные сертификаты',
            ];

            if ($this->itemHandler) {
                $attributes = call_user_func($this->itemHandler, [$attributes, $experience]);
            }
        }

        return $this->getItemXml($attributes);
    }

    /**
     * @param array $attributes
     * @return string
     */
    public function getItemXml(array $attributes)
    {
        $result = '<entry>' . "\n";

        foreach ($attributes as $attrName => $attrValue) {
            $result .= sprintf("<g:%s>%s</g:%s>\n", $attrName, $attrValue, $attrName);
        }

        $result .= '</entry>';

        return $result;
    }
}