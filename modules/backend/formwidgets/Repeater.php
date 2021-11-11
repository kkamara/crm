<?php namespace Backend\FormWidgets;

use Lang;
use ApplicationException;
use Backend\Classes\FormWidgetBase;

/**
 * Repeater Form Widget
 *
 * @package october\backend
 * @author Alexey Bobkov, Samuel Georges
 */
class Repeater extends FormWidgetBase
{
    //
    // Configurable properties
    //

    /**
     * @var array Form field configuration
     */
    public $form;

    /**
     * @var string Prompt text for adding new items.
     */
    public $prompt = 'backend::lang.repeater.add_new_item';

    /**
     * @var bool Items can be sorted.
     */
    public $sortable = false;

    /**
     * @var string Field name to use for the title of collapsed items
     */
    public $titleFrom = false;

    /**
     * @var int Minimum items required. Pre-displays those items when not using groups
     */
    public $minItems;

    /**
     * @var int Maximum items permitted
     */
    public $maxItems;

    /**
     * @var string The style of the repeater. Can be one of three values:
     *  - "default": Shows all repeater items expanded on load.
     *  - "collapsed": Shows all repeater items collapsed on load.
     *  - "accordion": Shows only the first repeater item expanded on load. When another item is clicked, all other open
     *      items are collapsed.
     */
    public $style;

    //
    // Object properties
    //

    /**
     * @inheritDoc
     */
    protected $defaultAlias = 'repeater';

    /**
     * @var array Meta data associated to each field, organised by index
     */
    protected $indexMeta = [];

    /**
     * @var array Collection of form widgets.
     */
    protected $formWidgets = [];

    /**
     * @var bool Stops nested repeaters populating from previous sibling.
     */
    protected static $onAddItemCalled = false;

    /**
     * @var bool useGroups
     */
    protected $useGroups = false;

    /**
     * @var array groupDefinitions
     */
    protected $groupDefinitions = [];

    /**
     * @var boolean isLoaded is true when the request is made via postback
     */
    protected $isLoaded = false;

    /**
     * @inheritDoc
     */
    public function init()
    {
        $this->fillFromConfig([
            'form',
            'style',
            'prompt',
            'sortable',
            'titleFrom',
            'minItems',
            'maxItems',
        ]);

        if ($this->formField->disabled) {
            $this->previewMode = true;
        }

        $this->processGroupMode();

        $this->processLoadedState();

        // First pass will contain postback, then raw attributes
        // This occurs to bind widgets to the controller early
        if (!self::$onAddItemCalled) {
            $this->processItems();
        }
    }

    /**
     * @inheritDoc
     */
    public function render()
    {
        $this->prepareVars();
        return $this->makePartial('repeater');
    }

    /**
     * prepareVars for display
     */
    public function prepareVars()
    {
        // Second pass will contain filtered attributes, then postback
        // This occurs to apply filtered values to the widget data
        if (!self::$onAddItemCalled) {
            $this->processItems();
        }

        if ($this->previewMode) {
            foreach ($this->formWidgets as $widget) {
                $widget->previewMode = true;
            }
        }

        $this->vars['name'] = $this->getFieldName();
        $this->vars['prompt'] = $this->prompt;
        $this->vars['formWidgets'] = $this->formWidgets;
        $this->vars['titleFrom'] = $this->titleFrom;
        $this->vars['minItems'] = $this->minItems;
        $this->vars['maxItems'] = $this->maxItems;
        $this->vars['style'] = $this->style;

        $this->vars['useGroups'] = $this->useGroups;
        $this->vars['groupDefinitions'] = $this->groupDefinitions;
    }

    /**
     * @inheritDoc
     */
    protected function loadAssets()
    {
        $this->addCss('css/repeater.css', 'core');
        $this->addJs('js/repeater.js', 'core');
    }

    /**
     * @inheritDoc
     */
    public function getSaveValue($value)
    {
        return $this->processSaveValue($value);
    }

    /**
     * processLoadedState is special logic that occurs during a postback,
     * the form field value is set directly from the postback data, this occurs
     * during initialization so that nested form widgets can be bound to the controller.
     */
    protected function processLoadedState()
    {
        if (!post($this->alias . '_loaded')) {
            return;
        }

        $this->formField->value = post($this->formField->getName());
        $this->isLoaded = true;
    }

    /**
     * processSaveValue splices in some meta data (group and index values) to the dataset
     * @param array $value
     * @return array|null
     */
    protected function processSaveValue($value)
    {
        if (!is_array($value) || !$value) {
            return null;
        }

        if ($this->minItems && count($value) < $this->minItems) {
            throw new ApplicationException(Lang::get('backend::lang.repeater.min_items_failed', [
                'name' => $this->fieldName,
                'min' => $this->minItems,
                'items' => count($value)
            ]));
        }

        if ($this->maxItems && count($value) > $this->maxItems) {
            throw new ApplicationException(Lang::get('backend::lang.repeater.max_items_failed', [
                'name' => $this->fieldName,
                'max' => $this->maxItems,
                'items' => count($value)
            ]));
        }

        /*
         * Give repeated form field widgets an opportunity to process the data.
         */
        foreach ($value as $index => $data) {
            if (isset($this->formWidgets[$index])) {
                if ($this->useGroups) {
                    $value[$index] = array_merge($this->formWidgets[$index]->getSaveData(), ['_group' => $data['_group']]);
                }
                else {
                    $value[$index] = $this->formWidgets[$index]->getSaveData();
                }
            }
        }

        return array_values($value);
    }

    /**
     * processItems processes data and applies it to the form widgets
     */
    protected function processItems()
    {
        $currentValue = $this->getLoadValue();

        // This lets record finder work inside a repeater with some hacks
        // since record finder spawns outside the form and its AJAX calls
        // don't reinitialize this repeater's items. We a need better way
        // remove if year >= 2023 -sg
        $handler = $this->controller->getAjaxHandler();
        if (!$this->isLoaded && starts_with($handler, $this->alias . 'Form')) {
            $handler = str_after($handler, $this->alias . 'Form');
            preg_match("~^(\d+)~", $handler, $matches);

            if (isset($matches[1])) {
                $index = $matches[1];
                $this->makeItemFormWidget($index);
            }
        }

        // Pad current value with minimum items and disable for groups,
        // which cannot predict their item types
        if (!$this->useGroups && $this->minItems > 0) {
            if (!is_array($currentValue)) {
                $currentValue = [];
            }

            if (count($currentValue) < $this->minItems) {
                $currentValue = array_pad($currentValue, $this->minItems, []);
            }
        }

        // Repeater value is empty or invalid
        if ($currentValue === null || !is_array($currentValue)) {
            $this->formWidgets = [];
            return;
        }

        // Load up the necessary form widgets
        foreach ($currentValue as $index => $value) {
            $this->makeItemFormWidget($index, array_get($value, '_group', null));
        }
    }

    /**
     * makeItemFormWidget creates a form widget based on a field index and optional group code
     * @param int $index
     * @param string $index
     * @return \Backend\Widgets\Form
     */
    protected function makeItemFormWidget($index = 0, $groupCode = null)
    {
        $configDefinition = $this->useGroups
            ? $this->getGroupFormFieldConfig($groupCode)
            : $this->form;

        $config = $this->makeConfig($configDefinition);
        $config->model = $this->model;
        $config->data = $this->getValueFromIndex($index);
        $config->alias = $this->alias . 'Form' . $index;
        $config->arrayName = $this->getFieldName().'['.$index.']';
        $config->isNested = true;

        if (self::$onAddItemCalled || $this->minItems > 0) {
            $config->enableDefaults = true;
        }

        $widget = $this->makeWidget(\Backend\Widgets\Form::class, $config);
        $widget->previewMode = $this->previewMode;
        $widget->bindToController();

        $this->indexMeta[$index] = [
            'groupCode' => $groupCode
        ];

        return $this->formWidgets[$index] = $widget;
    }

    /**
     * getValueFromIndex returns the data at a given index
     * @param int $index
     */
    protected function getValueFromIndex($index)
    {
        $value = $this->getLoadValue();

        if (!is_array($value)) {
            $value = [];
        }

        return array_get($value, $index, []);
    }

    //
    // AJAX handlers
    //

    /**
     * onAddItem handler
     */
    public function onAddItem()
    {
        self::$onAddItemCalled = true;

        $groupCode = post('_repeater_group');

        $index = $this->getNextIndex();

        $this->prepareVars();
        $this->vars['widget'] = $this->makeItemFormWidget($index, $groupCode);
        $this->vars['indexValue'] = $index;

        $itemContainer = '@#' . $this->getId('items');

        return [
            $itemContainer => $this->makePartial('repeater_item')
        ];
    }

    /**
     * onRemoveItem
     */
    public function onRemoveItem()
    {
        // Useful for deleting relations
    }

    /**
     * onRefresh
     */
    public function onRefresh()
    {
        $index = post('_repeater_index');
        $group = post('_repeater_group');

        $widget = $this->makeItemFormWidget($index, $group);

        return $widget->onRefresh();
    }

    /**
     * getNextIndex determines the next available index number for assigning to a
     * new repeater item
     */
    protected function getNextIndex(): int
    {
        $data = $this->getLoadValue();

        if (is_array($data) && count($data)) {
            return max(array_keys($data)) + 1;
        }

        return 0;
    }

    //
    // Group mode
    //

    /**
     * getGroupFormFieldConfig returns the form field configuration for a group, identified by code
     * @param string $code
     * @return array|null
     */
    protected function getGroupFormFieldConfig($code)
    {
        if (!$code) {
            return null;
        }

        $fields = array_get($this->groupDefinitions, $code.'.fields');

        if (!$fields) {
            return null;
        }

        return ['fields' => $fields, 'enableDefaults' => object_get($this->config, 'enableDefaults')];
    }

    /**
     * processGroupMode processes features related to group mode
     */
    protected function processGroupMode()
    {
        $palette = [];

        if (!$group = $this->getConfig('groups', [])) {
            $this->useGroups = false;
            return;
        }

        if (is_string($group)) {
            $group = $this->makeConfig($group);
        }

        foreach ($group as $code => $config) {
            $palette[$code] = [
                'code' => $code,
                'name' => array_get($config, 'name'),
                'icon' => array_get($config, 'icon', 'icon-square-o'),
                'description' => array_get($config, 'description'),
                'fields' => array_get($config, 'fields')
            ];
        }

        $this->groupDefinitions = $palette;
        $this->useGroups = true;
    }

    /**
     * getGroupCodeFromIndex returns a field group code from its index
     * @param $index int
     * @return string
     */
    public function getGroupCodeFromIndex($index)
    {
        return array_get($this->indexMeta, $index.'.groupCode');
    }

    /**
     * getGroupTitle returns the group title from its unique code
     * @param $groupCode string
     * @return string
     */
    public function getGroupTitle($groupCode)
    {
        return array_get($this->groupDefinitions, $groupCode.'.name');
    }
}
