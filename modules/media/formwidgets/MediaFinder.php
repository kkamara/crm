<?php namespace Media\FormWidgets;

use BackendAuth;
use Media\Classes\MediaLibrary;
use Backend\Classes\FormField;
use Backend\Classes\FormWidgetBase;

/**
 * Media Finder
 * Renders a record finder field.
 *
 *    image:
 *        label: Some image
 *        type: media
 *
 * @package october\media
 * @author Alexey Bobkov, Samuel Georges
 */
class MediaFinder extends FormWidgetBase
{
    //
    // Configurable properties
    //

    /**
     * @var string Display mode for the selection. Values: file, image.
     */
    public $mode = 'file';

    /**
     * @var int Preview image width
     */
    public $imageWidth = 190;

    /**
     * @var int Preview image height
     */
    public $imageHeight = 190;

    //
    // Object properties
    //

    /**
     * @inheritDoc
     */
    protected $defaultAlias = 'media';

    /**
     * @inheritDoc
     */
    public function init()
    {
        $this->fillFromConfig([
            'mode'
        ]);

        $user = BackendAuth::getUser();

        if ($this->formField->disabled || !$user || !$user->hasAccess('media.manage_media')) {
            $this->previewMode = true;
        }
    }

    /**
     * @inheritDoc
     */
    public function render()
    {
        $this->prepareVars();

        return $this->makePartial('mediafinder');
    }

    /**
     * prepareVars for display
     */
    public function prepareVars()
    {
        $value = $this->getLoadValue();
        $isImage = $this->mode === 'image';

        $this->vars['value'] = $value;
        $this->vars['imageUrl'] = $isImage && $value ? MediaLibrary::url($value) : '';
        $this->vars['imageExists'] = $isImage && $value ? MediaLibrary::instance()->exists($value) : '';
        $this->vars['field'] = $this->formField;
        $this->vars['mode'] = $this->mode;
        $this->vars['imageWidth'] = $this->imageWidth;
        $this->vars['imageHeight'] = $this->imageHeight;
    }

    /**
     * @inheritDoc
     */
    protected function loadAssets()
    {
        $this->addJs('js/mediafinder.js', 'core');
        $this->addCss('css/mediafinder.css', 'core');
    }
}
