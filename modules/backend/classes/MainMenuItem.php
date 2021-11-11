<?php namespace Backend\Classes;

use October\Rain\Exception\SystemException;

/**
 * MainMenuItem
 *
 * @package october\backend
 * @author Alexey Bobkov, Samuel Georges
 */
class MainMenuItem
{
    /**
     * @var string code
     */
    public $code;

    /**
     * @var string owner
     */
    public $owner;

    /**
     * @var string label
     */
    public $label;

    /**
     * @var null|string icon
     */
    public $icon;

    /**
     * @var null|string iconSvg
     */
    public $iconSvg;

    /**
     * @var mixed counter
     */
    public $counter;

    /**
     * @var null|string counterLabel
     */
    public $counterLabel;

    /**
     * @var string url
     */
    public $url;

    /**
     * @var array permissions
     */
    public $permissions = [];

    /**
     * @var int order
     */
    public $order = 500;

    /**
     * @var SideMenuItem[] sideMenu
     */
    public $sideMenu = [];

    /**
     * @var array customData
     */
    public $customData = [];

    /**
     * useConfig
     */
    public function useConfig(array $data): MainMenuItem
    {
        $this->code = $data['code'] ?? $this->code;
        $this->owner = $data['owner'] ?? $this->owner;
        $this->label = $data['label'] ?? $this->label;
        $this->url = $data['url'] ?? $this->url;
        $this->icon = $data['icon'] ?? $this->icon;
        $this->iconSvg = $data['iconSvg'] ?? $this->iconSvg;
        $this->counter = $data['counter'] ?? $this->counter;
        $this->counterLabel = $data['counterLabel'] ?? $this->counterLabel;
        $this->permissions = $data['permissions'] ?? $this->permissions;
        $this->order = $data['order'] ?? $this->order;
        $this->customData = $data['customData'] ?? $this->customData;

        return $this;
    }

    /**
     * addPermission
     * @param string $permission
     * @param array $definition
     */
    public function addPermission(string $permission, array $definition)
    {
        $this->permissions[$permission] = $definition;
    }

    /**
     * addSideMenuItem
     * @param SideMenuItem $sideMenu
     */
    public function addSideMenuItem(SideMenuItem $sideMenu)
    {
        $this->sideMenu[$sideMenu->code] = $sideMenu;
    }

    /**
     * getSideMenuItem
     * @param string $code
     * @return SideMenuItem
     * @throws SystemException
     */
    public function getSideMenuItem(string $code)
    {
        if (!array_key_exists($code, $this->sideMenu)) {
            throw new SystemException('No sidenavigation item available with code ' . $code);
        }

        return $this->sideMenu[$code];
    }

    /**
     * removeSideMenuItem
     * @param string $code
     */
    public function removeSideMenuItem(string $code)
    {
        unset($this->sideMenu[$code]);
    }
}
