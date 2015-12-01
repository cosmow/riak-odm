<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace Doctrine\ODM\Riak\Types;

use CosmoW\ODM\Riak\Mapping\MappingException;

/**
 * The Type interface.
 *
 * @since       1.0
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 * @author      Roman Borschel <roman@code-factory.org>
 */
abstract class Type
{
    const ID = 'id';
    const INTID = 'int_id';
    const CUSTOMID = 'custom_id';
    const BOOL = 'bool';
    const BOOLEAN = 'boolean';
    const INT = 'int';
    const INTEGER = 'integer';
    const FLOAT = 'float';
    const STRING = 'string';
    const DATE = 'date';
    const KEY = 'key';
    const TIMESTAMP = 'timestamp';
    const BINDATA = 'bin';
    const BINDATAFUNC = 'bin_func';
    const BINDATABYTEARRAY = 'bin_bytearray';
    const BINDATAUUID = 'bin_uuid';
    const BINDATAUUIDRFC4122 = 'bin_uuid_rfc4122';
    const BINDATAMD5 = 'bin_md5';
    const BINDATACUSTOM = 'bin_custom';
    const FILE = 'file';
    const HASH = 'hash';
    const COLLECTION = 'collection';
    const INCREMENT = 'increment';
    const OBJECTID = 'object_id';
    const RAW = 'raw';

    /** Map of already instantiated type objects. One instance per type (flyweight). */
    private static $typeObjects = array();

    /** The map of supported doctrine mapping types. */
    private static $typesMap = array(
        self::ID => 'Doctrine\ODM\Riak\Types\IdType',
        self::INTID => 'Doctrine\ODM\Riak\Types\IntIdType',
        self::CUSTOMID => 'Doctrine\ODM\Riak\Types\CustomIdType',
        self::BOOL => 'Doctrine\ODM\Riak\Types\BooleanType',
        self::BOOLEAN => 'Doctrine\ODM\Riak\Types\BooleanType',
        self::INT => 'Doctrine\ODM\Riak\Types\IntType',
        self::INTEGER => 'Doctrine\ODM\Riak\Types\IntType',
        self::FLOAT => 'Doctrine\ODM\Riak\Types\FloatType',
        self::STRING => 'Doctrine\ODM\Riak\Types\StringType',
        self::DATE => 'Doctrine\ODM\Riak\Types\DateType',
        self::KEY => 'Doctrine\ODM\Riak\Types\KeyType',
        self::TIMESTAMP => 'Doctrine\ODM\Riak\Types\TimestampType',
        self::BINDATA => 'Doctrine\ODM\Riak\Types\BinDataType',
        self::BINDATAFUNC => 'Doctrine\ODM\Riak\Types\BinDataFuncType',
        self::BINDATABYTEARRAY => 'Doctrine\ODM\Riak\Types\BinDataByteArrayType',
        self::BINDATAUUID => 'Doctrine\ODM\Riak\Types\BinDataUUIDType',
        self::BINDATAUUIDRFC4122 => 'Doctrine\ODM\Riak\Types\BinDataUUIDRFC4122Type',
        self::BINDATAMD5 => 'Doctrine\ODM\Riak\Types\BinDataMD5Type',
        self::BINDATACUSTOM => 'Doctrine\ODM\Riak\Types\BinDataCustomType',
        self::FILE => 'Doctrine\ODM\Riak\Types\FileType',
        self::HASH => 'Doctrine\ODM\Riak\Types\HashType',
        self::COLLECTION => 'Doctrine\ODM\Riak\Types\CollectionType',
        self::INCREMENT => 'Doctrine\ODM\Riak\Types\IncrementType',
        self::OBJECTID => 'Doctrine\ODM\Riak\Types\ObjectIdType',
        self::RAW => 'Doctrine\ODM\Riak\Types\RawType',
    );

    /* Prevent instantiation and force use of the factory method. */
    final private function __construct() {}

    /**
     * Converts a value from its PHP representation to its database representation
     * of this type.
     *
     * @param mixed $value The value to convert.
     * @return mixed The database representation of the value.
     */
    public function convertToDatabaseValue($value)
    {
        return $value;
    }

    /**
     * Converts a value from its database representation to its PHP representation
     * of this type.
     *
     * @param mixed $value The value to convert.
     * @return mixed The PHP representation of the value.
     */
    public function convertToPHPValue($value)
    {
        return $value;
    }

    public function closureToMongo()
    {
        return '$return = $value;';
    }

    public function closureToPHP()
    {
        return '$return = $value;';
    }

    /**
     * Register a new type in the type map.
     *
     * @param string $name The name of the type.
     * @param string $class The class name.
     */
    public static function registerType($name, $class)
    {
        self::$typesMap[$name] = $class;
    }

    /**
     * Get a Type instance.
     *
     * @param string $type The type name.
     * @return \Doctrine\ODM\Riak\Types\Type $type
     * @throws \InvalidArgumentException
     */
    public static function getType($type)
    {
        if ( ! isset(self::$typesMap[$type])) {
            throw new \InvalidArgumentException(sprintf('Invalid type specified "%s".', $type));
        }
        if ( ! isset(self::$typeObjects[$type])) {
            $className = self::$typesMap[$type];
            self::$typeObjects[$type] = new $className;
        }
        return self::$typeObjects[$type];
    }

    /**
     * Get a Type instance based on the type of the passed php variable.
     *
     * @param mixed $variable
     * @return \Doctrine\ODM\Riak\Types\Type $type
     * @throws \InvalidArgumentException
     */
    public static function getTypeFromPHPVariable($variable)
    {
        if (is_object($variable)) {
            if ($variable instanceof \DateTime) {
                return self::getType('date');
            } elseif ($variable instanceof \MongoId) {
                return self::getType('id');
            }
        } else {
            $type = gettype($variable);
            switch ($type) {
                case 'integer';
                    return self::getType('int');
            }
        }
        return null;
    }

    public static function convertPHPToDatabaseValue($value)
    {
        $type = self::getTypeFromPHPVariable($value);
        if ($type !== null) {
            return $type->convertToDatabaseValue($value);
        }
        return $value;
    }

    /**
     * Adds a custom type to the type map.
     *
     * @static
     * @param string $name Name of the type. This should correspond to what getName() returns.
     * @param string $className The class name of the custom type.
     * @throws MappingException
     */
    public static function addType($name, $className)
    {
        if (isset(self::$typesMap[$name])) {
            throw MappingException::typeExists($name);
        }

        self::$typesMap[$name] = $className;
    }

    /**
     * Checks if exists support for a type.
     *
     * @static
     * @param string $name Name of the type
     * @return boolean TRUE if type is supported; FALSE otherwise
     */
    public static function hasType($name)
    {
        return isset(self::$typesMap[$name]);
    }

    /**
     * Overrides an already defined type to use a different implementation.
     *
     * @static
     * @param string $name
     * @param string $className
     * @throws MappingException
     */
    public static function overrideType($name, $className)
    {
        if ( ! isset(self::$typesMap[$name])) {
            throw MappingException::typeNotFound($name);
        }

        self::$typesMap[$name] = $className;
    }

    /**
     * Get the types array map which holds all registered types and the corresponding
     * type class
     *
     * @return array $typesMap
     */
    public static function getTypesMap()
    {
        return self::$typesMap;
    }

    public function __toString()
    {
        $e = explode('\\', get_class($this));
        return str_replace('Type', '', end($e));
    }
}
