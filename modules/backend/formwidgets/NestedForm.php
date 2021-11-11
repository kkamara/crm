<?php namespace Backend\FormWidgets;

use Backend\Widgets\Form;
use Backend\Classes\FormWidgetBase;

/**
 * NestedForm renders a nested form bound to a jsonable field of a model
 *
 * @package october\backend
 * @author Alexey Bobkov, Samuel Georges, Sascha Aeppli
 */
class NestedForm extends FormWidgetBase
{
    use \Backend\Traits\FormModelWidget;

    /**
     * @inheritDoc
     */
    protected $defaultAlias = 'nestedform';

    /**
     * @var array form configuration
     */
    public $form;

    /**
     * @var bool showPanel defines if the nested form is styled like a panel
     */
    public $showPanel = true;

    /**
     * @var boolean useRelation will instruct the widget to look for a relationship
     */
    public $useRelation = false;

    /**
     * @var Form formWidget reference
     */
    protected $formWidget;

    /**
     * @inheritDoc
     */
    public function init()
    {
        $this->fillFromConfig([
            'form',
            'showPanel',
            'useRelation'
        ]);

        if ($this->formField->disabled) {
            $this->previewMode = true;
        }

        $config = $this->makeConfig($this->form);
        $config->model = $this->model;
        $config->data = $this->getLoadValue();
        $config->alias = $this->alias . $this->defaultAlias;
        $config->arrayName = $this->getFieldName();
        $config->isNested = true;

        // Pull config from parent
        if ($this->getParentForm()->getConfig('enableDefaults') === true) {
            $config->enableDefaults = true;
        }

        $widget = $this->makeWidget(Form::class, $config);
        $widget->previewMode = $this->previewMode;
        $widget->bindToController();

        $this->formWidget = $widget;
    }

    /**
     * prepareVars for display
     */
    public function prepareVars()
    {
        $this->formWidget->previewMode = $this->previewMode;
    }

    /**
     * getLoadValue
     */
    public function getLoadValue()
    {
        if ($this->useRelation) {
            [$model, $attribute] = $this->resolveModelAttribute($this->valueFrom);
            return $model->{$attribute};
        }

        return parent::getLoadValue();
    }

    /**
     * loadAssets
     */
    protected function loadAssets()
    {
        $this->addCss('css/nestedform.css', 'core');
    }

    /**
     * @inheritdoc
     */
    public function render()
    {
        $this->prepareVars();
        return $this->makePartial('nestedform');
    }
}
