<?php namespace System\Facades;

use October\Rain\Support\Facade;

/**
 * System facade
 * @see \System\Helpers\System
 */
class System extends Facade
{
    /**
     * @var string VERSION for October CMS, including major and minor.
     */
    const VERSION = '2.1';

    /**
     * getFacadeAccessor gets the registered name of the component.
     */
    protected static function getFacadeAccessor()
    {
        return 'system.helper';
    }
}
