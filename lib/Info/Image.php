<?php
/**
 * File containing the mm\Info\Image class
 */

namespace mm\Info;

class Image
{
    /**
     * Image type (fanart/poster)
     */
    public $type;

    /**
     * HTTP URL to the full image
     * @var string
     */
    public $fullUrl;

    /**
     * public $thumbnailURL
     * @var string
     */
    public $thumbnailUrl;

    /**
     * Image width
     * @var int
     */
    public $width;

    /**
     * Image height
     * @var int
     */
    public $height;
}
?>