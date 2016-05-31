<?php
namespace Dukhanin\Menu;

class MenuHelper
{

    public $collectionClass = 'Dukhanin\Menu\MenuCollection';

    public $itemClass = 'Dukhanin\Menu\MenuItem';

    protected $instances;


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
        $menu            = new $this->collectionClass;
        $menu->itemClass = $this->itemClass;
        $key             = strval($key);

        if (is_array($config = config('laravel-menu.' . $key))) {
            foreach ($config as $itemKey => $item) {
                $menu->put($itemKey, $item);
            }
        }

        return $menu;
    }

}