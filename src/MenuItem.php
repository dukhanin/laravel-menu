<?php
namespace Dukhanin\Menu;

use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;

class MenuItem
{

    protected $items;

    protected $params = [ ];


    public function __construct($item)
    {
        $item = $this->value($item);

        if (is_string($item)) {
            $item = [ 'label' => $item ];
        }

        if ( ! isset( $item['active'] )) {
            $item['active'] = [ get_class($this), 'itemActive' ];
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

        if (isset( $item['items'] ) && ! empty( $subitems = $this->value($item['items']) )) {
            foreach ($subitems as $key => $subitem) {
                $this->items()->put($key, $subitem);
            }

            unset( $item['items'] );
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


    public static function itemActive($item)
    {
        if ( ! is_null($item->route)) {
            return Route::current()->getName() == $item->route;
        }

        if ( ! is_null($item->action)) {
            return ends_with(Route::current()->getActionName(), $item->action);
        }

        if ( ! is_null($item->url)) {
            $currentPath = trim(Request::path(), '/');
            $path        = trim(parse_url($item->url, PHP_URL_PATH), '/');

            return starts_with($currentPath, $path);
        }

        return false;
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


    public function __call($name, $arguments)
    {
        return $this->get($name);
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
