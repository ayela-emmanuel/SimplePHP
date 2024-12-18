<?php
namespace Internal\Utils;

class DataTransformer
{
    /**
     * Deserialize the data array into an object of the given type.
     * @template T
     * @param array<string, mixed> $data The array to deserialize.
     * @param class-string<T> $modelType The target class name.
     * @return T The deserialized object.
     */
    public static function deserialize(array $data, string $modelType)
    {
        if (!class_exists($modelType)) {
            throw new \InvalidArgumentException("Class {$modelType} does not exist.");
        }

        $object = new $modelType();

        foreach ($data as $key => $value) {
            if (property_exists($modelType, $key)) {
                $object->{$key} = $value;
            } else {
                throw new \InvalidArgumentException("Unexpected Value {$key} From Input Data.");
            }
        }

        if (method_exists($object, 'validate')) {
            $object->validate();
        }

        return $object;
    }

    /**
     * Serialize an object into an associative array.
     * @param object $object The object to serialize.
     * @return array<string, mixed> The serialized array.
     */
    public static function serialize(object $object): array
    {
        $reflectionClass = new \ReflectionClass($object);
        $properties = $reflectionClass->getProperties();

        $data = [];
        foreach ($properties as $property) {
            $property->setAccessible(true);
            $data[$property->getName()] = $property->getValue($object);
        }

        return $data;
    }

    /**
     * Validate data against required properties of the model.
     * @template T
     * @param array<string, mixed> $data The array to validate.
     * @param class-string<T> $modelType The target class name.
     * @return void
     */
    public static function validateData(array $data, string $modelType): void
    {
        if (!class_exists($modelType)) {
            throw new \InvalidArgumentException("Class {$modelType} does not exist.");
        }

        $reflectionClass = new \ReflectionClass($modelType);
        $properties = $reflectionClass->getProperties();
        $requiredProperties = array_map(fn($prop) => $prop->getName(), $properties);

        $missingProperties = array_diff($requiredProperties, array_keys($data));
        if (!empty($missingProperties)) {
            throw new \InvalidArgumentException("Missing required properties: " . implode(", ", $missingProperties));
        }
    }

    /**
     * Map data from one array to another format based on a mapping configuration.
     * @param array<string, mixed> $data The source data.
     * @param array<string, string> $mapping An associative array mapping source keys to target keys.
     * @return array<string, mixed> The transformed array.
     */
    public static function mapData(array $data, array $mapping): array
    {
        $transformed = [];

        foreach ($mapping as $sourceKey => $targetKey) {
            if (array_key_exists($sourceKey, $data)) {
                $transformed[$targetKey] = $data[$sourceKey];
            }
        }

        return $transformed;
    }
}
