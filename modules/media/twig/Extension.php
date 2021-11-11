<?php namespace Media\Twig;

use Twig\Extension\AbstractExtension as TwigExtension;
use Twig\TwigFilter as TwigSimpleFilter;
use Media\Classes\MediaLibrary;

/**
 * The Media Twig extension class implements common Twig functions and filters.
 *
 * @package october\media
 * @author Alexey Bobkov, Samuel Georges
 */
class Extension extends TwigExtension
{
    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return [];
    }

    /**
     * Returns a list of filters this extensions provides.
     *
     * @return array An array of filters
     */
    public function getFilters()
    {
        $filters = [
            new TwigSimpleFilter('media', [$this, 'mediaFilter'], ['is_safe' => ['html']]),
        ];

        return $filters;
    }

    /**
     * Returns a list of token parsers this extensions provides.
     *
     * @return array An array of token parsers
     */
    public function getTokenParsers()
    {
        return [];
    }

    /**
     * Converts supplied file to a URL relative to the media library.
     * @param string $file Specifies the media-relative file
     * @return string
     */
    public function mediaFilter($file)
    {
        return MediaLibrary::url($file);
    }
}
