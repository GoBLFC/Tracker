<?php
/**
 * This file is part of the TelegramBot package.
 *
 * (c) Avtandil Kikabidze aka LONGMAN <akalongman@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Longman\TelegramBot\Entities;

class Entity
{
    protected $bot_name;

    public function getBotName()
    {
        return $this->bot_name;
    }

    public function toJson()
    {
        $fields = $this->reflect($this);
        $json = json_encode($fields);

        return $json;
    }

    public function reflect($object = null)
    {
        if ($object == null) {
            $object = $this;
        }

        $reflection = new \ReflectionObject($object);
        $properties = $reflection->getProperties();

        $fields = [];

        foreach ($properties as $property) {
            $name = $property->getName();
            if ($name == 'bot_name') {
                continue;
            }

            if (!$property->isPrivate()) {
                $array_of_obj = false;
                $array_of_array_obj = false;
                if (is_array($object->$name)) {
                    $array_of_obj = true;
                    $array_of_array_obj = true;
                    foreach ($object->$name as $elm) {
                        if (!is_object($elm)) {
                            //echo $name . " not array of object \n";
                            $array_of_obj = false;
                            //break;
                        }
                        if (is_array($elm)) {
                            foreach ($elm as $more_net) {
                                if (!is_object($more_net)) {
                                    $array_of_array_obj = false;
                                }
                            }
                        }
                    }
                }

                if (is_object($object->$name)) {
                    $fields[$name] = $this->reflect($object->$name);
                } elseif ($array_of_obj) {
                    foreach ($object->$name as $elm) {
                        $fields[$name][] = $this->reflect($elm);
                    }
                } elseif ($array_of_array_obj) {
                    foreach ($object->$name as $elm) {
                        $temp = null;
                        if (!is_array($elm) && !is_object($elm)) {
                            continue;
                        }
                        foreach ($elm as $obj) {
                            $temp[] = $this->reflect($obj);
                        }
                        $fields[$name][] = $temp;
                    }
                } else {
                    $property->setAccessible(true);
                    $value = $property->getValue($object);
                    if (is_null($value)) {
                        continue;
                    }
                    $fields[$name] = $value;
                }
            }
        }
        return $fields;
    }

    public function __toString()
    {
        return $this->toJson();
    }
}
