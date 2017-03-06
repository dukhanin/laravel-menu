<?php
namespace Dukhanin\Menu;

use Dukhanin\Support\Traits\ResolvedCollection;
use Illuminate\Support\Collection;

class MenuCollection extends Collection
{

    use ResolvedCollection;

    public $itemClass = MenuItem::class;


    public function __construct($items = [])
    {
        $this->resolver(function ($item, $key) {
            if ($item instanceof MenuItem) {
                return $item;
            }

            $className = $this->itemClass;

            return new $className($item);
        });

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
                $this->put($firstKeySegment, []);
            }

            $this->offsetGet($firstKeySegment)->items()->offsetSet(implode('.', $keySegments), $value);
        } else {
            parent::offsetSet($key, $this->resolve($value, $key));
        }
    }


    public function offsetUnset($key)
    {
        $keySegments     = explode('.', $key);
        $firstKeySegment = array_shift($keySegments);

        if ($keySegments && $this->offsetExists($firstKeySegment)) {
            parent::offsetUnset($firstKeySegment)->items()->offsetUnset(implode('.', $keySegments));
        } else {
            parent::offsetUnset($key);
        }
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
        $value       = $this->resolve($value, $key);
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
        $value       = $this->resolve($value, $key);
        $this->items = array_after($this->items, $key, $value, $keyAfter);

        return $this;
    }

}