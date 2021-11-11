<?php namespace Backend\FormWidgets;

use Db;
use Backend\Classes\FormField;
use Backend\Classes\FormWidgetBase;

/**
 * Relation renders a field prepopulated with a belongsTo and belongsToHasMany relation
 *
 * @package october\backend
 * @author Alexey Bobkov, Samuel Georges
 */
class Relation extends FormWidgetBase
{
    use \Backend\Traits\FormModelWidget;

    //
    // Configurable properties
    //

    /**
     * @var string nameFrom is the model column to use for the name reference
     */
    public $nameFrom = 'name';

    /**
     * @var string sqlSelect is the custom SQL column selection to use for the name reference
     */
    public $sqlSelect;

    /**
     * @var string emptyOption to use if the relation is singluar (belongsTo)
     */
    public $emptyOption;

    /**
     * @var string scope method for the list query.
     */
    public $scope;

    /**
     * @var string order of the list query.
     */
    public $order;

    //
    // Object properties
    //

    /**
     * @inheritDoc
     */
    protected $defaultAlias = 'relation';

    /**
     * @var FormField renderFormField object used for rendering a simple field type
     */
    public $renderFormField;

    /**
     * @inheritDoc
     */
    public function init()
    {
        $this->fillFromConfig([
            'nameFrom',
            'emptyOption',
            'scope',
            'order',
        ]);

        if (isset($this->config->select)) {
            $this->sqlSelect = $this->config->select;
        }
    }

    /**
     * @inheritDoc
     */
    public function render()
    {
        $this->prepareVars();
        return $this->makePartial('relation');
    }

    /**
     * prepareVars for display
     */
    public function prepareVars()
    {
        $this->vars['field'] = $this->makeRenderFormField();
    }

    /**
     * makeRenderFormField for rendering a simple field type
     */
    protected function makeRenderFormField()
    {
        $field = clone $this->formField;
        [$model, $attribute] = $this->resolveModelAttribute($this->valueFrom);

        $relationObject = $this->getRelationObject();
        $relationType = $model->getRelationType($attribute);
        $relationModel = $model->makeRelation($attribute);
        $query = $relationModel->newQuery();

        if (in_array($relationType, ['belongsToMany', 'morphToMany', 'morphedByMany', 'hasMany'])) {
            $field->type = 'checkboxlist';
        }
        elseif (in_array($relationType, ['belongsTo', 'hasOne'])) {
            $field->type = 'dropdown';
        }

        // Order query by the configured option.
        if ($this->order) {
            // Using "raw" to allow authors to use a string to define the order clause.
            $query->orderByRaw($this->order);
        }

        // It is safe to assume that if the model and related model are of
        // the exact same class, then it cannot be related to itself
        if ($model->exists && (get_class($model) == get_class($relationModel))) {
            $query->where($relationModel->getKeyName(), '<>', $model->getKey());
        }

        if ($scopeMethod = $this->scope) {
            $query->$scopeMethod($model);
        }
        else {
            $relationObject->addDefinedConstraintsToQuery($query);
        }

        // Determine if the model uses a tree trait
        $treeTraits = ['October\Rain\Database\Traits\NestedTree', 'October\Rain\Database\Traits\SimpleTree'];
        $usesTree = count(array_intersect($treeTraits, class_uses($relationModel))) > 0;

        // The "sqlSelect" config takes precedence over "nameFrom".
        // A virtual column called "selection" will contain the result.
        // Tree models must select all columns to return parent columns, etc.
        if ($this->sqlSelect) {
            $nameFrom = 'selection';
            $selectColumn = $usesTree ? '*' : $relationModel->getKeyName();
            $result = $query->select($selectColumn, Db::raw($this->sqlSelect . ' AS ' . $nameFrom));
        }
        else {
            $nameFrom = $this->nameFrom;
            $result = $query->get();
        }

        // Some simpler relations can specify a custom local or foreign "other" key,
        // which can be detected and implemented here automagically.
        $primaryKeyName = in_array($relationType, ['hasMany', 'belongsTo', 'hasOne'])
            ? $relationObject->getOtherKey()
            : $relationModel->getKeyName();

        $field->options = $usesTree
            ? $result->listsNested($nameFrom, $primaryKeyName)
            : $result->lists($nameFrom, $primaryKeyName);

        return $this->renderFormField = $field;
    }

    /**
     * @inheritDoc
     */
    public function getSaveValue($value)
    {
        if (is_string($value) && !strlen($value)) {
            return null;
        }

        if (is_array($value) && !count($value)) {
            return null;
        }

        return $value;
    }
}
