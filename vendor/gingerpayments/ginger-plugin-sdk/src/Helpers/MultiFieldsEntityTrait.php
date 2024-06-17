<?php

namespace GingerPluginSdk\Helpers;

use GingerPluginSdk\Bases\BaseField;
use GingerPluginSdk\Interfaces\MultiFieldsEntityInterface;

trait MultiFieldsEntityTrait
{
    use HelperTrait;
    public array $notAllowedProperties = [
        'events',
        'is_capturable',
        'order_id',
        'project_id',
        'flags'
    ];
    public function getPropertyName(): string
    {
        return $this->propertyName ?? false;
    }

    public function toArray(): array
    {
        $response = [];
        foreach (get_object_vars($this) as $var) {
            if ($var instanceof BaseField) {
                $response[$var->getPropertyName()] = $var->get();
            } elseif ($var instanceof MultiFieldsEntityInterface) {
                $response[$var->getPropertyName()] = $var->toArray();
            }
        }

        return array_filter($response, function ($value) {
            return ($value !== null && $value !== []);
        });
    }

    public function filterAdditionalProperties($additionalProperties): void
    {
        foreach ($additionalProperties as $key => $value) {
            $key = $this->dashesToCamelCase($key, true);

            // Check if $additionalProperties is already an entity class
            $path_to_properties = \GingerPluginSdk\Client::PROPERTIES_PATH . $key;
            $path_to_collection = \GingerPluginSdk\Client::COLLECTIONS_PATH . $key;
            $path_to_entities = \GingerPluginSdk\Client::ENTITIES_PATH . $key;

            if (class_exists($path_to_properties)) {
                $propertyValue = $value instanceof $path_to_properties ? $value : new $path_to_properties($value);
                $this->{$key} = $propertyValue;
            } elseif (class_exists($path_to_collection)) {
                if ($value instanceof $path_to_collection) {
                    $this->{$key} = $value;
                } elseif ($this->isAssoc($value)) {
                    $this->{$key} = new $path_to_collection($value);
                } else {
                    $collection = new $path_to_collection();
                    foreach ($value as $item) {
                        $collection->add($item);
                    }
                    $this->{$key} = $collection;
                }
            } elseif (class_exists($path_to_entities)) {
                $entityValue = $value instanceof $path_to_entities ? $value : new $path_to_entities(...$value);
                $this->{$key} = $entityValue;
            } else {
                // Handle as a simple field if the class doesn't exist
                $this->{$key} = $this->createSimpleField(
                    propertyName: $this->camelCaseToDashes($key),
                    value: $value
                );
            }
        }
    }

    function removeKeysRecursive(array &$array, array $keysToRemove): void {
        foreach ($array as $key => &$value) {
            if (in_array($key, $keysToRemove)) {
                unset($array[$key]);
            } elseif (is_array($value)) {
                // If the value is an array, recursively remove keys from it
                $this->removeKeysRecursive($value, $keysToRemove);
            }
        }
    }


    public function update(...$attributes): static
    {
        foreach ($attributes as $key => $value) {
            $upped_key = $this->dashesToCamelCase($key);
            // Block if we need just update key property with a new value.
            if (isset($this->$upped_key)) {
                if ($this->$upped_key instanceof MultiFieldsEntityInterface) {
                    $this->$upped_key->update($value);
                } else {
                    $this->$upped_key->set($value);
                }
                // Block if we need to assign key property with updated value.
            } else {
                $this->filterAdditionalProperties([$key => $value]);
            }
        }
        return $this;
    }
}