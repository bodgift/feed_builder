<?php

namespace common\extensions\feedBuilder\formats;


use common\models\City;
use common\models\Experience;
use common\models\Price;

/**
 * Class TrafmagXmlFormat
 * @package common\extensions\feedBuilder\formats
 */
class TrafmagXmlFormat implements FormatInterface
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
        . ' <catalog>' . PHP_EOL
        . '<categories>' . PHP_EOL
        . '<category id="1">main</category>' . PHP_EOL
        . '</categories>' . PHP_EOL
        . '<offers>';
    }

    /**
     * @return string
     */
    public function getFooter()
    {
        return '</offers>' . PHP_EOL
        . '</catalog>';
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

            $experienceUrl = $experience->getUrl(true, APP_FRONTEND_NAME);
            $attributes = [
                'id'         => $experience->id,
                'url'        => htmlspecialchars($experienceUrl . $this->urlAffix, ENT_QUOTES | ENT_XML1),
                'oldprice'   => '',
                'skidka'     => '',
                'price'      => $experience->price,
                'currencyId' => Price::getCurrency(),
                'categoryId' => 1,
                'picture'    => $experience->getFilterThumbnailBig()->getSrc(true),
                'vendor'     => '<![CDATA[' . html_entity_decode(strip_tags($name)) . ']]>',
                'model'      => 'Яркая дизайнерская упаковка. Подарки-впечатления без наценок',
            ];

            if ($this->itemHandler) {
                $attributes = call_user_func($this->itemHandler, [$attributes, $experience]);
            }
        }

        return $this->getItemXml($attributes, $experience->isActive());
    }

    /**
     * @param array $attributes
     * @param $isActive
     * @return string
     */
    public function getItemXml(array $attributes, $isActive)
    {
        $result = '<offer id="' . $attributes['id'] . '" available="' . ($isActive ? 'true' : 'false') . '">' . PHP_EOL;
        unset($attributes['id']);

        foreach ($attributes as $attrName => $attrValue) {
            $result .= sprintf("<%s>%s</%s>\n", $attrName, $attrValue, $attrName);
        }

        $result .= '</offer>';

        return $result;
    }
}