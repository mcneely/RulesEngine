<?php declare(strict_types=1);

namespace McNeely\Rules;

use McNeely\Rules\Exceptions\UndefinedPropertyException;

class ExtendedObject
{
    private object $object;

    public function __construct(object $object)
    {
        $this->object = $object;
    }

    /**
     * @return object
     */
    public function getObject(): object
    {
        return $this->object;
    }

    public function isInstanceOf(string $class): bool
    {
        return $this->object instanceof $class;
    }

    /**
     * @param string $name
     * @return false|mixed
     * @throws UndefinedPropertyException
     */
    public function __get(string $name)
    {
        if(array_key_exists($name, get_object_vars($this->object))) {
            return get_object_vars($this->object)[$name];
        }

        $getter = "get$name";
        return $this->__call($getter);
    }

    /**
     * @param string $name
     * @param mixed  $value
     * @throws UndefinedPropertyException
     */
    public function __set(string $name, $value): void
    {
        if(array_key_exists($name, get_object_vars($this->object))) {
            $this->object->$name = $value;
            return;
        }

        $setter = "set$name";
        $this->__call($setter, [$value]);
    }

    /**
     * @param string $name
     * @param array<int, mixed>  $arguments
     * @return false|mixed
     * @throws UndefinedPropertyException
     */
    public function __call(string $name, array $arguments = [])
    {
        if(method_exists($this->object, $name)) {
            return $this->object->$name(...$arguments);
        }

        throw new UndefinedPropertyException(get_class($this->object) . "::\$$name");
    }

    /**
     * @param string $name
     * @return bool
     */
    public function __isset(string $name)
    {
        try
        {
            return !(null === $this->__get($name));
        } catch (UndefinedPropertyException $e) {
            return false;
        }
    }
}