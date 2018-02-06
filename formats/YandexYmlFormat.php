<?php

namespace common\extensions\feedBuilder\formats;


use common\models\City;
use common\models\Experience;
use common\models\ExperienceVsDirection;
use common\models\Price;

class YandexYmlFormat implements FormatInterface
{
    public $city;
    public $urlAffix = '';
    public $titlePrefix = 'Подарочный сертификат на ';

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
        return '<?xml version="1.0" encoding="utf-8"?>' . "\n" . '<!DOCTYPE yml_catalog SYSTEM "shops.dtd">' . "\n" . '
        <yml_catalog date="' . date('Y-m-d H:i') . '">
            <shop>
                <name>Интернет магазин подарков Bodo.ua</name>
                <company>BODO</company>
                <url>' . \Yii::$app->urlManagerRouter->get(APP_FRONTEND_NAME)->city($this->city->subdomain)->getCombinedHostInfo() . '/</url>
                <currencies>
                  <currency id="UAH" rate="1" plus="0"/>
                </currencies>
                <categories>
                  <category id="1">Экстрим</category>
                  <category id="2">Романтика</category>
                  <category id="3">Прочее</category>
                </categories>
                <pickup>true</pickup>' . ($this->city->is_default ? '<local_delivery_cost>40</local_delivery_cost>' : '') . '
                <offers>
        ';
    }

    /**
     * @return string
     */
    public function getFooter()
    {
        $result = "</offers>\n";
        $result .= "</shop>\n";
        $result .= "</yml_catalog>\n";

        return $result;
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
                'id'          => $experience->id,
                'url'         => htmlspecialchars($experienceUrl . $this->urlAffix, ENT_QUOTES | ENT_XML1),
                'price'       => $experience->price,
                'currencyId'  => Price::getCurrency(),
                'picture'     => $experience->getFilterThumbnailBig()->getSrc(true),
                'delivery'    => $this->city->is_default ? 'true' : 'false',
                'name'        => '<![CDATA[' . html_entity_decode(strip_tags($name)) . ']]>',
                'description' => '<![CDATA[' . html_entity_decode(strip_tags($experience->brief)) . ']]>',
            ];

            if ($this->itemHandler) {
                $attributes = call_user_func($this->itemHandler, $attributes, $experience);
            }
        }

        return $this->getItemXml($attributes, $experience->isActive());
    }

    /**
     * @param array $attributes
     * @param bool  $available
     * @return string
     */
    public function getItemXml(array $attributes, $available = true)
    {
        $result = '<offer id="' . $attributes['id'] . '" available="' . ($available ? 'true' : 'false') . '" bid="21">' . "\n";
        unset($attributes['id']);

        foreach ($attributes as $attrName => $attrValue) {
            $result .= sprintf("<%s>%s</%s>\n", $attrName, $attrValue, $attrName);
        }
        $result .= '</offer>';

        return $result;
    }
}