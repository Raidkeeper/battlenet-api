<?php declare(strict_types=1);

namespace Raidkeeper\Api\Battlenet;

class ApiResponse
{
    protected \stdClass $data;
    protected string    $locale;

    public function __construct(\stdClass $data, string $locale)
    {
        $this->data   = $data;
        $this->locale = $locale;
    }

    public function __get(string $name): string|null
    {
        if (isset($this->data->$name)) {
            $found = $this->data->$name;
            if (is_object($found)) {
                if (isset($found->name)) {
                    return $this->getLocaleData($found);
                } elseif (isset($found->href)) {
                    return $found->href;
                }
            } else {
                return $this->data->$name;
            }
        }
        throw new Error('Could not smartly return '.$name.' from api data. Try getRaw() instead.', 404);
    }

    public function getRaw(string $name): mixed
    {
        return $this->data->$name;
    }

    protected function getLocaleData(object $object): string|null
    {
        $locale = $this->locale;
        if (isset($object->name)) {
            if (isset($object->name->$locale)) {
                return $object->name->$locale;
            }
        }
        return null;
    }
}
