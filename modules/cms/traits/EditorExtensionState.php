<?php namespace Cms\Traits;

use App;
use Lang;
use Cms\Classes\Page;
use Cms\Classes\Partial;
use Cms\Classes\Content;
use Cms\Classes\Layout;
use Cms\Classes\EditorExtension;
use Cms\Classes\ComponentHelpers;
use Cms\Classes\ComponentManager;
use System\Classes\PluginManager;
use Backend\VueComponents\TreeView\NodeDefinition;
use Backend\VueComponents\DropdownMenu\ItemDefinition;
use Editor\Classes\NewDocumentDescription;

/**
 * EditorExtensionState initializes state for the CMS Editor Extension
 */
trait EditorExtensionState
{
    private function getCmsPageNewDocumentData()
    {
        $description = new NewDocumentDescription(
            trans('cms::lang.page.new'),
            $this->makeMetadataForNewTemplate(EditorExtension::DOCUMENT_TYPE_PAGE)
        );

        // Always inject a view bag so that custom template properties
        // defined using the CMS extensibility API can use it.
        // Empty view bags get automatically removed from templates
        // before they are saved.
        //
        $manager = ComponentManager::instance();
        $viewBagComponent = $this->makeTemplateComponent($manager, 'viewBag', [], 'viewBag');

        $description->setIcon(EditorExtension::ICON_COLOR_PAGE, 'backend-icon-background entity-small cms-page');
        $description->setInitialDocumentData([
            'title' => trans('cms::lang.page.new'),
            'components' => [
                $viewBagComponent
            ],
            'code' => '',
            'markup' => ''
        ]);

        return $description;
    }

    private function getCmsPartialNewDocumentData()
    {
        $description = new NewDocumentDescription(
            trans('cms::lang.partial.new'),
            $this->makeMetadataForNewTemplate(EditorExtension::DOCUMENT_TYPE_PARTIAL)
        );

        $description->setIcon(EditorExtension::ICON_COLOR_PARTIAL, 'backend-icon-background entity-small cms-partial');
        $description->setInitialDocumentData([
            'fileName' => 'new-partial.htm',
            'components' => [
                $this->getViewBagComponent()
            ],
            'code' => '',
            'markup' => ''
        ]);

        return $description;
    }

    private function getCmsLayoutNewDocumentData()
    {
        $description = new NewDocumentDescription(
            trans('cms::lang.layout.new'),
            $this->makeMetadataForNewTemplate(EditorExtension::DOCUMENT_TYPE_LAYOUT)
        );

        $description->setIcon(EditorExtension::ICON_COLOR_LAYOUT, 'backend-icon-background entity-small cms-layout');
        $description->setInitialDocumentData([
            'fileName' => 'new-layout.htm',
            'components' => [],
            'code' => '',
            'markup' => ''
        ]);

        return $description;
    }

    private function getCmsContentNewDocumentData()
    {
        $description = new NewDocumentDescription(
            trans('cms::lang.content.new'),
            $this->makeMetadataForNewTemplate(EditorExtension::DOCUMENT_TYPE_CONTENT)
        );

        $description->setIcon(EditorExtension::ICON_COLOR_CONTENT, 'backend-icon-background entity-small cms-content');
        $description->setInitialDocumentData([
            'fileName' => 'new-content.htm',
            'components' => [],
            'markup' => ''
        ]);

        return $description;
    }

    private function addPagesNavigatorNodes($theme, $section)
    {
        $pages = Page::listInTheme($theme, true);
        $pagesNode = $section->addNode(Lang::get('cms::lang.page.editor_node_name'), EditorExtension::DOCUMENT_TYPE_PAGE);
        $pagesNode
            ->setSortBy('title')
            ->setGroupBy('path')
            ->setGroupByMode(NodeDefinition::GROUP_BY_MODE_FOLDERS)
            ->setChildKeyPrefix(EditorExtension::DOCUMENT_TYPE_PAGE.':')
        ;

        $pagesNode->addRootMenuItem(ItemDefinition::TYPE_TEXT,
            Lang::get('cms::lang.page.create_new'), 'cms:create-document@'.EditorExtension::DOCUMENT_TYPE_PAGE);
        $pagesNode->addRootMenuItem(ItemDefinition::TYPE_SEPARATOR);

        $sortingItem = $pagesNode->addRootMenuItem(ItemDefinition::TYPE_TEXT,
            Lang::get('cms::lang.page.editor_sorting'), 'sorting');
        $sortingItem->addItem(ItemDefinition::TYPE_RADIOBUTTON,
            Lang::get('cms::lang.page.editor_sort_by_title'), 'cms:sort-pages@title')
            ->setChecked(true);
        $sortingItem->addItem(ItemDefinition::TYPE_RADIOBUTTON,
            Lang::get('cms::lang.page.editor_sort_by_url'), 'cms:sort-pages@url');
        $sortingItem->addItem(ItemDefinition::TYPE_RADIOBUTTON,
            Lang::get('cms::lang.page.editor_sort_by_file_name'), 'cms:sort-pages@filename');

        $groupingItem = $pagesNode->addRootMenuItem(ItemDefinition::TYPE_TEXT,
            Lang::get('cms::lang.page.editor_grouping'), 'grouping');
        $groupingItem->addItem(ItemDefinition::TYPE_RADIOBUTTON,
            Lang::get('cms::lang.page.editor_group_by_filepath'), 'cms:group-pages@path')
            ->setChecked(true);
        $groupingItem->addItem(ItemDefinition::TYPE_RADIOBUTTON,
            Lang::get('cms::lang.page.editor_group_by_url'), 'cms:group-pages@url');

        $displayItem = $pagesNode->addRootMenuItem(ItemDefinition::TYPE_TEXT,
            Lang::get('cms::lang.page.editor_display'), 'display');
        $displayItem->addItem(ItemDefinition::TYPE_RADIOBUTTON,
            Lang::get('cms::lang.page.editor_display_title'), 'cms:display-pages@title')
            ->setChecked(true);
        $displayItem->addItem(ItemDefinition::TYPE_RADIOBUTTON,
            Lang::get('cms::lang.page.editor_display_url'), 'cms:display-pages@url');
        $displayItem->addItem(ItemDefinition::TYPE_RADIOBUTTON,
            Lang::get('cms::lang.page.editor_display_file'), 'cms:display-pages@filename');

        foreach ($pages as $page) {
            $pagePath = dirname($page->fileName);
            if ($pagePath == '.') {
                $pagePath = "";
            }

            $title = strlen($page->title) ? $page->title : 'No title';

            $pagesNode
                ->addNode($title, $page->getFileName())
                ->setIcon(EditorExtension::ICON_COLOR_PAGE, 'backend-icon-background entity-small cms-page')
                ->setUserData([
                    'title' => $title,
                    'url' => $page->url,
                    'filename' => $page->fileName,
                    'path' => $pagePath
                ])
            ;
        }
    }

    private function addLayoutsNavigatorNodes($theme, $section)
    {
        $layouts = Layout::listInTheme($theme, true);
        $layoutsNode = $section->addNode(Lang::get('cms::lang.layout.editor_node_name'), EditorExtension::DOCUMENT_TYPE_LAYOUT);
        $layoutsNode
            ->setSortBy('filename')
            ->setGroupBy('path')
            ->setGroupByMode(NodeDefinition::GROUP_BY_MODE_FOLDERS)
            ->setChildKeyPrefix(EditorExtension::DOCUMENT_TYPE_LAYOUT.':')
        ;

        $layoutsNode->addRootMenuItem(ItemDefinition::TYPE_TEXT,
            Lang::get('cms::lang.layout.create_new'), 'cms:create-document@'.EditorExtension::DOCUMENT_TYPE_LAYOUT);

        foreach ($layouts as $layout) {
            $layoutPath = dirname($layout->fileName);
            if ($layoutPath == '.') {
                $layoutPath = "";
            }

            $layoutsNode
                ->addNode($layout->getFileName(), $layout->getFileName())
                ->setIcon(EditorExtension::ICON_COLOR_LAYOUT, 'backend-icon-background entity-small cms-layout')
                ->setUserData([
                    'title' => $layout->getFileName(),
                    'filename' => $layout->fileName,
                    'path' => $layoutPath
                ])
            ;
        }
    }

    private function addPartialsNavigatorNodes($theme, $section)
    {
        $partials = Partial::listInTheme($theme, true);
        $partialsNode = $section->addNode(Lang::get('cms::lang.partial.editor_node_name'), EditorExtension::DOCUMENT_TYPE_PARTIAL);
        $partialsNode
            ->setSortBy('filename')
            ->setGroupBy('path')
            ->setGroupByMode(NodeDefinition::GROUP_BY_MODE_FOLDERS)
            ->setChildKeyPrefix(EditorExtension::DOCUMENT_TYPE_PARTIAL.':');

        $partialsNode->addRootMenuItem(ItemDefinition::TYPE_TEXT,
            Lang::get('cms::lang.partial.create_new'), 'cms:create-document@'.EditorExtension::DOCUMENT_TYPE_PARTIAL);

        foreach ($partials as $partial) {
            $partialPath = dirname($partial->fileName);
            if ($partialPath == '.') {
                $partialPath = "";
            }

            $partialsNode
                ->addNode($partial->getFileName(), $partial->getFileName())
                ->setIcon(EditorExtension::ICON_COLOR_PARTIAL, 'backend-icon-background entity-small cms-partial')
                ->setUserData([
                    'title' => $partial->getFileName(),
                    'filename' => $partial->fileName,
                    'path' => $partialPath
                ])
            ;
        }
    }

    private function addContentNavigatorNodes($theme, $section)
    {
        $contents = Content::listInTheme($theme, true);
        $contentNode = $section->addNode(Lang::get('cms::lang.content.editor_node_name'), EditorExtension::DOCUMENT_TYPE_CONTENT);
        $contentNode
            ->setSortBy('filename')
            ->setGroupBy('path')
            ->setGroupByMode(NodeDefinition::GROUP_BY_MODE_FOLDERS)
            ->setChildKeyPrefix(EditorExtension::DOCUMENT_TYPE_CONTENT.':');

        $contentNode->addRootMenuItem(ItemDefinition::TYPE_TEXT,
            Lang::get('cms::lang.content.new'), 'cms:create-document@'.EditorExtension::DOCUMENT_TYPE_CONTENT);

        foreach ($contents as $contentFile) {
            $contentPath = dirname($contentFile->fileName);
            if ($contentPath == '.') {
                $contentPath = "";
            }

            $contentNode
                ->addNode($contentFile->getFileName(), $contentFile->getFileName())
                ->setIcon(EditorExtension::ICON_COLOR_CONTENT, 'backend-icon-background entity-small cms-content')
                ->setUserData([
                    'title' => $contentFile->getFileName(),
                    'filename' => $contentFile->fileName,
                    'path' => $contentPath
                ]);
        }
    }

    private function loadLayoutsForUiLists($theme, $user)
    {
        if ($user->hasAnyAccess(['cms.manage_layouts'])) {
            // Use layout list from Navigator
            return [];
        }

        $layouts = Layout::listInTheme($theme, true);

        $result = [];
        foreach ($layouts as $layout) {
            $result[] = $layout->fileName;
        }

        return $result;
    }

    private function loadPartialsForUiLists($theme, $user)
    {
        if ($user->hasAnyAccess(['cms.manage_partials'])) {
            // Use partial list from Navigator
            return [];
        }

        $partials = Partial::listInTheme($theme, true);

        $result = [];
        foreach ($partials as $partial) {
            $result[] = $partial->fileName;
        }

        return $result;
    }

    private function loadContentForUiLists($theme, $user)
    {
        if ($user->hasAnyAccess(['cms.manage_content'])) {
            // Use content file list from Navigator
            return [];
        }

        $contentFiles = Content::listInTheme($theme, true);

        $result = [];
        foreach ($contentFiles as $contentFile) {
            $result[] = $contentFile->fileName;
        }

        return $result;
    }

    private function loadPagesForUiLists($theme, $user)
    {
        if ($user->hasAnyAccess(['cms.manage_pages'])) {
            // Use page list from Navigator
            return [];
        }

        $pages = Page::listInTheme($theme, true);

        $result = [];
        foreach ($pages as $page) {
            $result[] = $page->fileName;
        }

        return $result;
    }

    private function loadComponentsForUiLists()
    {
        $rootNode = new NodeDefinition('Components', 'cms-components');
        $rootNode->setGroupBy('plugin');

        $pluginManager = PluginManager::instance();
        $plugins = $pluginManager->getPlugins();

        $knownAliases = [];
        foreach ($plugins as $plugin) {
            $components = $plugin->registerComponents();
            if (!is_array($components) || !count($components)) {
                continue;
            }

            $pluginDetails = $plugin->pluginDetails();
            $pluginName = trans($pluginDetails['name']) ?? trans('system::lang.plugin.unnamed');
            $pluginClass = get_class($plugin);
            $pluginIcon = $pluginDetails['icon'] ?? 'icon-puzzle-piece';

            $pluginNode = $rootNode->addNode($pluginName, $pluginClass);
            $pluginNode
                ->setSelectable(false)
                ->setDisplayMode(NodeDefinition::DISPLAY_MODE_LIST)
                ->setDragAndDropMode([NodeDefinition::DND_CUSTOM]);

            foreach ($components as $className => $alias) {
                $component = App::make($className);

                $componentName = trans(ComponentHelpers::getComponentName($component));
                $componentDescription = trans(ComponentHelpers::getComponentDescription($component));
                $componentNode = $pluginNode->addNode($componentName, $className);
                $componentNode
                    ->setDescription($componentDescription)
                    ->setSelectable(false);

                $duplicateAlias = array_key_exists($alias, $knownAliases);
                $userData = [
                    'plugin' => $pluginName,
                    'nodeSearchData'     => $componentName.' '.$pluginName.' '.$componentDescription,
                    'componentData' => [
                        'alias'            => $alias,
                        'className'        => $className,
                        'description'      => $componentDescription,
                        'icon'             => $pluginIcon,
                        'inspectorEnabled' => true,
                        'name'             => $duplicateAlias ? $className : $alias,
                        'propertyConfig'   => ComponentHelpers::getComponentsPropertyConfig($component),
                        'propertyValues'   => ComponentHelpers::getComponentPropertyValues($component, true),
                        'title'            => $componentName
                    ]
                ];

                $knownAliases[$alias] = 1;
                $componentNode->setUserData($userData);
            }
        }

        return $rootNode->toArray()['nodes'];
    }
}
