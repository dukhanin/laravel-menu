<?php
namespace Dukhanin\Menu;

class MenuHelper
{

    protected $instances;

    protected $config;


    public function __construct()
    {
    }


    public function get($key = 'default')
    {
        $key = strval($key);

        if ( ! isset( $this->instances[$key] )) {
            $this->instances[$key] = $this->factoryMenu($key);
        }

        return $this->instances[$key];
    }


    public function factoryMenu($key)
    {
        $menu = new MenuItemCollection;
        $key  = strval($key);

        if (is_array($config = config('laravel-menu.' . $key))) {
            foreach ($config as $itemKey => $item) {
                $menu->put($itemKey, $item);
            }
        }

        return $menu;
    }

}