<?php
namespace Dukhanin\Menu;

class MenuItem
{

    protected $items;

    protected $label;

    protected $active;

    protected $url;

    protected $params = [ ];


    public function __construct($item)
    {
        if (is_callable($item)) {
            $item = call_user_func($item);
        }

        if (is_string($item)) {
            $item = [ 'label' => $item ];
        }

        if (isset( $item['label'] )) {
            $this->label = $item['label'];
        }

        if (isset( $item['url'] )) {
            $this->url = $item['url'];
        }

        if (isset( $item['active'] )) {
            $this->active = $item['active'];
        } else {
            $this->active = [ $this, 'activeDefaultHandler' ];
        }

        $this->set(array_except($item, [ 'label', 'url', 'active' ]));
    }


    public function label()
    {
        return $this->value($this->label);
    }


    public function url()
    {
        return $this->value($this->url);
    }


    public function active()
    {
        return $this->value($this->active);
    }


    public function items()
    {
        if (is_null($this->items)) {
            $this->initItems();
        }

        return $this->items;
    }


    public function raw($key)
    {
        $key = strval($key);

        if (property_exists($this, $key) && $key !== 'params') {
            return $this->{$key};
        }

        if (isset( $this->params[$key] )) {
            return $this->params[$key];
        }
    }


    public function get($key)
    {
        if (method_exists($this, $key)) {
            return $this->{$key}();
        }

        return $this->value( $this->raw($key) );
    }


    public function set($key, $value = null)
    {
        if (is_array($key)) {
            foreach ($key as $_key => $_value) {
                $this->set($_key, $_value);
            }

            return;
        }

        $key = strval($key);

        if (property_exists($this, $key) && $key !== 'params') {
            $this->{$key} = $value;
        } else {
            $this->params[$key] = $value;
        }
    }


    public function __get($key)
    {
        return $this->get($key);
    }


    public function __set($key, $value)
    {
        return $this->set($key, $value);
    }


    public function __call(string $name, array $arguments)
    {
        return $this->get($name);
    }


    protected function activeDefaultHandler()
    {
        // @todo
        return false;
    }


    protected function initItems()
    {
        $this->items = new MenuItemCollection();
    }


    protected function value($value)
    {
        if (is_callable($value)) {
            $value = call_user_func($value);
        }

        return $value;
    }

}