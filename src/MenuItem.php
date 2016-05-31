<?php
namespace Dukhanin\Menu;

use Route;
use Request;

class MenuItem
{

    protected $items;

    protected $params = [ ];


    public function __construct($item)
    {
        if (is_callable($item)) {
            $item = call_user_func($item);
        }

        if (is_string($item)) {
            $item = [ 'label' => $item ];
        }

        if ( ! isset( $item['active'] )) {
            $item['active'] = [ get_class($this), 'isActive' ];
        }

        if ( ! isset( $item['route'] )) {
            $item['route'] = null;
        }

        if ( ! isset( $item['action'] )) {
            $item['action'] = null;
        }

        if ( ! isset( $item['url'] )) {
            $item['url'] = null;
        }

        if ( ! isset( $item['enabled'] )) {
            $item['enabled'] = true;
        }

        $this->set($item);
    }


    public function url()
    {
        if ( ! is_null($this->params['url'])) {
            return $this->value($this->params['url']);
        }

        if ( ! is_null($this->route)) {
            return route($this->route);
        }

        if ( ! is_null($this->action)) {
            return action($this->action);
        }
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

        return $this->value($this->raw($key));
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


    public static function isActive($item)
    {
        if ( ! is_null($item->route)) {
            return Route::current()->getName() == $item->route;
        }

        if ( ! is_null($item->action)) {
            return Route::current()->getActionName() == $item->action;
        }

        if ( ! is_null($item->url)) {
            $currentPath = trim(Request::path(), '/');
            $path = trim(parse_url($item->url, PHP_URL_PATH), '/');

            return strpos($path, $currentPath) === 0;
        }

        return false;
    }


    protected function initItems()
    {
        $this->items = new MenuCollection();
    }


    protected function value($value)
    {
        if (is_callable($value)) {
            $value = call_user_func($value, $this);
        }

        return $value;
    }

}