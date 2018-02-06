<?php
namespace common\extensions\feedBuilder;


use common\extensions\feedBuilder\formats\FormatInterface;
use common\models\query\ExperienceQuery;

class FeedBuilder
{
    /**
     * Кол-во записей обрабатываемых за раз
     */
    const PORTION_SIZE = 111000;

    /**
     * Максимально возможное количество строк
     */
    const MAX_POSSIBLE = 1150000;

    /**
     * Генерация товарного фида
     * @param ExperienceQuery $activeQuery
     * @param FormatInterface $format
     * @return string
     */
    public static function make(ExperienceQuery $activeQuery, FormatInterface $format)
    {
        if (!$activeQuery->limit) {
            $activeQuery->limit = self::MAX_POSSIBLE;
        }

        $xml = $format->getHeader();

        foreach ($activeQuery->batch(self::PORTION_SIZE) as $experiencesGroup) {
            foreach ($experiencesGroup as $experienceModel) {

                $xml .= $format->getItem($experienceModel);
            }
        }

        $xml .= $format->getFooter();

        return $xml;
    }
}