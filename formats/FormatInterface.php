<?php
namespace common\extensions\feedBuilder\formats;

use common\models\Experience;

interface FormatInterface
{
    /**
     * @param Experience $experience
     * @return string
     */
    function getItem(Experience $experience);

    /**
     * @return string
     */
    function getHeader();

    /**
     * @return string
     */
    function getFooter();
}