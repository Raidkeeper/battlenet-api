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

    public function __get(string $name): mixed
    {
        if (isset($this->data->$name)) {
            $data = $this->data->$name;
            if (is_object($data)) {
                return $this->getFromObject($data);
            }
            return $this->data->$name;
        }
        return new \Error('Could not smartly return '.$name.' from api data. Try getRaw() instead.', 404);
    }

    public function getFromObject(mixed $data): mixed
    {
        if (isset($data->name)) {
            return is_string($data->name) ? $data->name : $this->getLocaleData($data);
        }
        return isset($data->href) ? $data->href : $data;
    }

    public function getRaw(string $name): mixed
    {
        return $this->data->$name;
    }

    public function getLocaleData(object $object): string|null
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
