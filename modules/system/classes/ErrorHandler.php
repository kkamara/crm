<?php namespace System\Classes;

use Log;
use View;
use Lang;
use System;
use Cms\Classes\Theme;
use Cms\Classes\Router;
use Cms\Classes\Controller as CmsController;
use October\Rain\Exception\ErrorHandler as ErrorHandlerBase;
use October\Rain\Exception\ApplicationException;
use October\Rain\Exception\SystemException;

/**
 * ErrorHandler handles application exception events
 *
 * @package october\system
 * @author Alexey Bobkov, Samuel Georges
 */
class ErrorHandler extends ErrorHandlerBase
{
    /**
     * @inheritDoc
     */
    // public function handleException(\Exception $proposedException)
    // {
    //     // The Twig runtime error is not very useful
    //     if (
    //         $proposedException instanceof \Twig\Error\RuntimeError &&
    //         ($previousException = $proposedException->getPrevious()) &&
    //         (!$previousException instanceof \Cms\Classes\CmsException)
    //     ) {
    //         $proposedException = $previousException;
    //     }
    //     return parent::handleException($proposedException);
    // }

    /**
     * beforeHandleError happens when we are about to display an error page to the user,
     * if it is an SystemException, this event should be logged.
     * @return void
     */
    public function beforeHandleError($exception)
    {
        if ($exception instanceof SystemException) {
            Log::error($exception);
        }
    }

    /**
     * handleCustomError looks up an error page using the CMS route "/error". If the route
     * does not exist, this function will use the error view found in the CMS module.
     * @return mixed Error page contents.
     */
    public function handleCustomError()
    {
        if (System::checkDebugMode()) {
            return null;
        }

        if (
            System::hasModule('Cms') &&
            ($theme = Theme::getActiveTheme())
        ) {
            $router = new Router($theme);

            // Use the default view if no "/error" URL is found.
            if (!$router->findByUrl('/error')) {
                return View::make('cms::error');
            }

            // Route to the CMS error page.
            $controller = new CmsController($theme);
            $result = $controller->run('/error');
        }
        else {
            $result = View::make('system::error');
        }

        // Extract content from response object
        if ($result instanceof \Symfony\Component\HttpFoundation\Response) {
            $result = $result->getContent();
        }

        return $result;
    }

    /**
     * handleDetailedError displays the detailed system exception page.
     * @return View Object containing the error page.
     */
    public function handleDetailedError($exception)
    {
        // Ensure System view path is registered
        View::addNamespace('system', base_path().'/modules/system/views');

        return View::make('system::exception', ['exception' => $exception]);
    }

    /**
     * getDetailedMessage returns a more descriptive error message based on the context.
     * @param Exception $exception
     * @return string
     */
    public static function getDetailedMessage($exception)
    {
        // ApplicationException never displays a detailed error
        if ($exception instanceof ApplicationException) {
            return $exception->getMessage();
        }

        // Debug mode is on
        if (System::checkDebugMode()) {
            return parent::getDetailedMessage($exception);
        }

        // Prevent database exceptions from leaking
        if ($exception instanceof \Illuminate\Database\QueryException) {
            return Lang::get('system::lang.page.custom_error.help');
        }

        return $exception->getMessage();
    }
}
