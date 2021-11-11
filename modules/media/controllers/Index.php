<?php namespace Media\Controllers;

use BackendMenu;
use Media\Widgets\MediaManager;
use Backend\Classes\Controller;

/**
 * Backend Media Manager
 *
 * @package october\media
 * @author Alexey Bobkov, Samuel Georges
 */
class Index extends Controller
{
    /**
     * @var array Permissions required to view this page.
     */
    public $requiredPermissions = ['media.*'];

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('October.Media', 'media', true);
        $this->pageTitle = 'backend::lang.media.menu_label';

        $manager = new MediaManager($this, 'manager');
        $manager->bindToController();
    }

    public function index()
    {
        $this->bodyClass = 'compact-container';
    }
}
