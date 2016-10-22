<?php
namespace Dukhanin\Menu;

class MenuHelper
{

    public $collectionClass = MenuCollection::class;

    public $itemClass = MenuItem::class;

    protected $instances = [ ];


    public function get($key = 'laravel-menu')
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

        if (is_array($config = config($key))) {
            foreach ($config as $itemKey => $item) {
                $menu->put($itemKey, $item);
            }
        }

        return $menu;
    }
}
