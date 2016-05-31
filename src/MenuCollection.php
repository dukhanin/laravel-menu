<?php
namespace Dukhanin\Menu;

use Illuminate\Support\Collection;

class MenuCollection extends Collection
{

    public $itemClass = 'Dukhanin\Menu\MenuItem';


    public function __construct($items = [ ])
    {
        foreach ($this->getArrayableItems($items) as $key => $value) {
            $this->offsetSet($key, $value);
        }
    }


    public function enabled()
    {
        return $this->filter(function ($item) {
            return $item->enabled;
        });
    }


    public function hasActive()
    {
        foreach ($this as $item) {
            if ($item->active || $item->items()->hasActive()) {
                return true;
            }
        }

        return false;
    }


    public function hasEnabled()
    {
        foreach ($this as $item) {
            if ($item->enabled || $item->items()->hasEnabled()) {
                return true;
            }
        }

        return false;
    }


    public function offsetExists($key)
    {
        $keySegments     = explode('.', $key);
        $firstKeySegment = array_shift($keySegments);

        if ($keySegments && $this->offsetExists($firstKeySegment)) {
            return parent::offsetGet($firstKeySegment)->items()->offsetExists(implode('.', $keySegments));
        } else {
            return parent::offsetExists($key);
        }
    }


    public function offsetGet($key)
    {
        $keySegments     = explode('.', $key);
        $firstKeySegment = array_shift($keySegments);

        if ($keySegments && $this->offsetExists($firstKeySegment)) {
            return parent::offsetGet($firstKeySegment)->items()->offsetGet(implode('.', $keySegments));
        } else {
            $item = parent::offsetGet($key);
            $item->set('key', $key);

            return $item;
        }
    }


    public function offsetSet($key, $value)
    {
        $keySegments     = explode('.', $key);
        $firstKeySegment = array_shift($keySegments);

        if ($keySegments) {
            if ( ! $this->offsetExists($firstKeySegment)) {
                $this->put($firstKeySegment, [ ]);
            }

            $this->offsetGet($firstKeySegment)->items()->offsetSet(implode('.', $keySegments), $value);
        } else {
            parent::offsetSet($key, $this->validateItem($value));
        }
    }


    public function offsetUnset($key)
    {
        $keySegments     = explode('.', $key);
        $firstKeySegment = array_shift($keySegments);

        if ($keySegments && $this->offsetExists($firstKeySegment)) {
            return parent::offsetGet($firstKeySegment)->items()->offsetUnset(implode('.', $keySegments));
        } else {
            return parent::offsetUnset($key);
        }
    }


    public function get($key, $default = null)
    {
        if ($this->offsetExists($key)) {
            return $this->offsetGet($key);
        }

        return value($default);
    }


    public function prepend($value, $key = null)
    {
        $value = $this->validateItem($value);

        return parent::prepend($value, $key);
    }


    public function before($key, $value, $keyBefore = null)
    {
        if (is_null($keyBefore)) {
            return $this->prepend($key, $value);
        }

        $keySegments = explode('.', $keyBefore);
        $lastKey     = array_pop($keySegments);
        $nestedKey   = implode('.', $keySegments);

        if ($nestedKey && $this->offsetExists($nestedKey)) {
            return $this->offsetGet($nestedKey)->items()->before($key, $value, $lastKey);
        }

        $key         = str_replace('.', '_', $key);
        $value       = $this->validateItem($value);
        $this->items = array_before($this->items, $key, $value, $keyBefore);

        return $this;
    }


    public function after($key, $value, $keyAfter = null)
    {
        if (is_null($keyAfter)) {
            return $this->put($key, $value);
        }

        $keySegments = explode('.', $keyAfter);
        $lastKey     = array_pop($keySegments);
        $nestedKey   = implode('.', $keySegments);

        if ($nestedKey && $this->offsetExists($nestedKey)) {
            return $this->offsetGet($nestedKey)->items()->after($key, $value, $lastKey);
        }

        $key         = str_replace('.', '_', $key);
        $value       = $this->validateItem($value);
        $this->items = array_after($this->items, $key, $value, $keyAfter);

        return $this;
    }


    protected function validateItem($item)
    {
        if ($item instanceof MenuItem) {
            return $item;
        }

        $className = $this->itemClass;

        return new $className($item);
    }


    protected function getArrayableItems($items)
    {
        $items = parent::getArrayableItems($items);

        $items = array_map([ $this, 'validateItem' ], $items);

        return $items;
    }
}