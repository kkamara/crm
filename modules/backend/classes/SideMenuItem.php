<?php namespace Backend\Classes;

/**
 * SideMenuItem
 *
 * @package october\backend
 * @author Alexey Bobkov, Samuel Georges
 */
class SideMenuItem
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
     * @var string url
     */
    public $url;

    /**
     * @var null|int|callable counter
     */
    public $counter;

    /**
     * @var null|string counterLabel
     */
    public $counterLabel;

    /**
     * @var int order
     */
    public $order = -1;

    /**
     * @var array attributes
     */
    public $attributes = [];

    /**
     * @var array permissions
     */
    public $permissions = [];

    /**
     * @var string itemType
     */
    public $itemType;

    /**
     * @var string buttonActiveOn
     */
    public $buttonActiveOn;

    /**
     * @var array customData
     */
    public $customData = [];

    /**
     * useConfig
     */
    public function useConfig(array $data): SideMenuItem
    {
        $this->code = $data['code'] ?? $this->code;
        $this->owner = $data['owner'] ?? $this->owner;
        $this->label = $data['label'] ?? $this->label;
        $this->url = $data['url'] ?? $this->url;
        $this->icon = $data['icon'] ?? $this->icon;
        $this->iconSvg = $data['iconSvg'] ?? $this->iconSvg;
        $this->counter = $data['counter'] ?? $this->counter;
        $this->counterLabel = $data['counterLabel'] ?? $this->counterLabel;
        $this->attributes = $data['attributes'] ?? $this->attributes;
        $this->permissions = $data['permissions'] ?? $this->permissions;
        $this->order = $data['order'] ?? $this->order;
        $this->itemType = $data['itemType'] ?? $this->itemType;
        $this->buttonActiveOn = $data['buttonActiveOn'] ?? $this->buttonActiveOn;
        $this->customData = $data['customData'] ?? $this->customData;

        return $this;
    }

    /**
     * addAttribute
     * @param null|string|int $attribute
     * @param null|string|array $value
     */
    public function addAttribute($attribute, $value)
    {
        $this->attributes[$attribute] = $value;
    }

    /**
     * removeAttribute
     */
    public function removeAttribute($attribute)
    {
        unset($this->attributes[$attribute]);
    }

    /**
     * addPermission
     */
    public function addPermission(string $permission, array $definition)
    {
        $this->permissions[$permission] = $definition;
    }

    /**
     * removePermission
     * @param string $permission
     * @return void
     */
    public function removePermission(string $permission)
    {
        unset($this->permissions[$permission]);
    }
}
