<?php namespace Backend\Behaviors\RelationController;

use Request;
use October\Rain\Database\Model;

/**
 * HasViewMode
 */
trait HasViewMode
{
    /**
     * @var Backend\Classes\WidgetBase viewWidget used for viewing (list or form)
     */
    protected $viewWidget;

    /**
     * @var \Backend\Widgets\Filter viewFilterWidget
     */
    protected $viewFilterWidget;

    /**
     * @var Model viewModel is a reference to the model used for viewing (form only)
     */
    protected $viewModel;

    /**
     * @var string viewMode if relation has many (multi) or has one (single)
     */
    protected $viewMode;

    /**
     * @var string forceViewMode
     */
    protected $forceViewMode;

    /**
     * relationGetViewWidget returns the view widget used by this behavior
     * @return \Backend\Classes\WidgetBase
     */
    public function relationGetViewWidget()
    {
        return $this->viewWidget;
    }

    /**
     * makeViewWidget
     */
    protected function makeViewWidget()
    {
        $widget = null;

        /*
         * Multiple (has many, belongs to many)
         */
        if ($this->viewMode === 'multi') {
            $config = $this->makeConfigForMode('view', 'list');
            $config->model = $this->relationModel;
            $config->alias = $this->alias . 'ViewList';
            $config->showSorting = $this->getConfig('view[showSorting]', true);
            $config->defaultSort = $this->getConfig('view[defaultSort]');
            $config->recordsPerPage = $this->getConfig('view[recordsPerPage]');
            $config->showCheckboxes = $this->getConfig('view[showCheckboxes]', !$this->readOnly);
            $config->recordUrl = $this->getConfig('view[recordUrl]', null);
            $config->customViewPath = $this->getConfig('view[customViewPath]', null);

            $defaultOnClick = sprintf(
                "$.oc.relationBehavior.clickViewListRecord(':%s', '%s', '%s')",
                $this->relationModel->getKeyName(),
                $this->relationGetId(),
                $this->relationGetSessionKey()
            );

            if ($config->recordUrl) {
                $defaultOnClick = null;
            }
            elseif (
                !$this->makeConfigForMode('manage', 'form', false) &&
                !$this->makeConfigForMode('pivot', 'form', false)
            ) {
                $defaultOnClick = null;
            }

            $config->recordOnClick = $this->getConfig('view[recordOnClick]', $defaultOnClick);

            if ($emptyMessage = $this->getConfig('emptyMessage')) {
                $config->noRecordsMessage = $emptyMessage;
            }

            $widget = $this->makeWidget(\Backend\Widgets\Lists::class, $config);

            /*
             * Apply defined constraints
             */
            if ($sqlConditions = $this->getConfig('view[conditions]')) {
                $widget->bindEvent('list.extendQueryBefore', function ($query) use ($sqlConditions) {
                    $query->whereRaw($sqlConditions);
                });
            }
            elseif ($scopeMethod = $this->getConfig('view[scope]')) {
                $widget->bindEvent('list.extendQueryBefore', function ($query) use ($scopeMethod) {
                    $query->$scopeMethod($this->model);
                });
            }
            else {
                $widget->bindEvent('list.extendQueryBefore', function ($query) {
                    $this->relationObject->addDefinedConstraintsToQuery($query);
                });
            }

            /*
             * Constrain the query by the relationship and deferred items
             */
            $widget->bindEvent('list.extendQuery', function ($query) {
                $this->relationObject->setQuery($query);

                $sessionKey = $this->deferredBinding ? $this->relationGetSessionKey() : null;

                if ($sessionKey) {
                    $this->relationObject->withDeferred($sessionKey);
                }
                elseif ($this->model->exists) {
                    $this->relationObject->addConstraints();
                }

                /*
                 * Allows pivot data to enter the fray
                 */
                if (in_array($this->relationType, ['belongsToMany', 'morphToMany', 'morphedByMany'])) {
                    $this->relationObject->setQuery($query->getQuery());
                    return $this->relationObject;
                }
            });

            /*
             * Constrain the list by the search widget, if available
             */
            if ($this->toolbarWidget && $this->getConfig('view[showSearch]')
                && $searchWidget = $this->toolbarWidget->getSearchWidget()
            ) {
                $searchWidget->bindEvent('search.submit', function () use ($widget, $searchWidget) {
                    $widget->setSearchTerm($searchWidget->getActiveTerm());
                    return $widget->onRefresh();
                });

                // Linkage for JS plugins
                $searchWidget->listWidgetId = $widget->getId();

                // Persist the search term across AJAX requests only
                if (Request::ajax()) {
                    $widget->setSearchTerm($searchWidget->getActiveTerm());
                }
                else {
                    $searchWidget->setActiveTerm(null);
                }
            }

            /*
             * Link the Filter Widget to the List Widget
             */
            if ($this->viewFilterWidget) {
                $this->viewFilterWidget->bindEvent('filter.update', function () use ($widget) {
                    return $widget->onFilter();
                });

                // Apply predefined filter values
                $widget->addFilter([$this->viewFilterWidget, 'applyAllScopesToQuery']);
            }
        }
        /*
         * Single (belongs to, has one)
         */
        elseif ($this->viewMode === 'single') {
            $this->viewModel = $this->relationObject->getResults()
                ?: $this->relationModel;

            $config = $this->makeConfigForMode('view', 'form');
            $config->model = $this->viewModel;
            $config->arrayName = class_basename($this->relationModel);
            $config->context = 'relation';
            $config->alias = $this->alias . 'ViewForm';

            $widget = $this->makeWidget(\Backend\Widgets\Form::class, $config);
            $widget->previewMode = true;
        }

        return $widget;
    }

    //
    // AJAX (Buttons)
    //

    /**
     * onRelationButtonAdd
     */
    public function onRelationButtonAdd()
    {
        $this->eventTarget = 'button-add';

        return $this->onRelationManageForm();
    }

    /**
     * onRelationButtonCreate
     */
    public function onRelationButtonCreate()
    {
        $this->eventTarget = 'button-create';

        return $this->onRelationManageForm();
    }

    /**
     * onRelationButtonDelete
     */
    public function onRelationButtonDelete()
    {
        return $this->onRelationManageDelete();
    }

    /**
     * onRelationButtonLink
     */
    public function onRelationButtonLink()
    {
        $this->eventTarget = 'button-link';

        return $this->onRelationManageForm();
    }

    /**
     * onRelationButtonUnlink
     */
    public function onRelationButtonUnlink()
    {
        return $this->onRelationManageRemove();
    }

    /**
     * onRelationButtonRemove
     */
    public function onRelationButtonRemove()
    {
        return $this->onRelationManageRemove();
    }

    /**
     * onRelationButtonUpdate
     */
    public function onRelationButtonUpdate()
    {
        $this->eventTarget = 'button-update';

        return $this->onRelationManageForm();
    }

    //
    // AJAX (List events)
    //

    /**
     * onRelationClickManageList
     */
    public function onRelationClickManageList()
    {
        return $this->onRelationManageAdd();
    }

    /**
     * onRelationClickManageListPivot
     */
    public function onRelationClickManageListPivot()
    {
        return $this->onRelationManagePivotForm();
    }

    /**
     * onRelationClickViewList
     */
    public function onRelationClickViewList()
    {
        $this->eventTarget = 'list';
        return $this->onRelationManageForm();
    }

    /**
     * evalViewMode determines the view mode based on the model relationship type
     * @return string
     */
    protected function evalViewMode()
    {
        if ($this->forceViewMode) {
            return $this->forceViewMode;
        }

        switch ($this->relationType) {
            case 'hasMany':
            case 'morphMany':
            case 'morphToMany':
            case 'morphedByMany':
            case 'belongsToMany':
            case 'hasManyThrough':
                return 'multi';

            case 'hasOne':
            case 'morphOne':
            case 'belongsTo':
                return 'single';
        }
    }

    /**
     * resetViewWidgetModel is an internal method used when deleting singular relationships
     */
    protected function resetViewWidgetModel()
    {
        $this->viewWidget->model = $this->relationModel;
        $this->viewWidget->setFormValues([]);
    }
}
