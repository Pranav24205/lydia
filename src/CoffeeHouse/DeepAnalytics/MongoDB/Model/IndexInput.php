<?php

    namespace MongoDB\Model;

    use MongoDB\BSON\Serializable;
    use MongoDB\Exception\InvalidArgumentException;
    use function is_array;
    use function is_float;
    use function is_int;
    use function is_object;
    use function is_string;
    use function MongoDB\generate_index_name;
    use function sprintf;

    /**
     * Index input model class.
     *
     * This class is used to validate user input for index creation.
     *
     * @internal
     * @see \MongoDB\Collection::createIndexes()
     * @see https://github.com/mongodb/specifications/blob/master/source/enumerate-indexes.rst
     * @see http://docs.mongodb.org/manual/reference/method/db.collection.createIndex/
     */
    class IndexInput implements Serializable
    {
        /** @var array */
        private $index;

        /**
         * @param array $index Index specification
         * @throws InvalidArgumentException
         */
        public function __construct(array $index)
        {
            if (! isset($index['key'])) {
                throw new InvalidArgumentException('Required "key" document is missing from index specification');
            }

            if (! is_array($index['key']) && ! is_object($index['key'])) {
                throw InvalidArgumentException::invalidType('"key" option', $index['key'], 'array or object');
            }

            foreach ($index['key'] as $fieldName => $order) {
                if (! is_int($order) && ! is_float($order) && ! is_string($order)) {
                    throw InvalidArgumentException::invalidType(sprintf('order value for "%s" field within "key" option', $fieldName), $order, 'numeric or string');
                }
            }

            if (! isset($index['ns'])) {
                throw new InvalidArgumentException('Required "ns" option is missing from index specification');
            }

            if (! is_string($index['ns'])) {
                throw InvalidArgumentException::invalidType('"ns" option', $index['ns'], 'string');
            }

            if (! isset($index['name'])) {
                $index['name'] = generate_index_name($index['key']);
            }

            if (! is_string($index['name'])) {
                throw InvalidArgumentException::invalidType('"name" option', $index['name'], 'string');
            }

            $this->index = $index;
        }

        /**
         * Return the index name.
         *
         * @return string
         */
        public function __toString()
        {
            return $this->index['name'];
        }

        /**
         * Serialize the index information to BSON for index creation.
         *
         * @see \MongoDB\Collection::createIndexes()
         * @see http://php.net/mongodb-bson-serializable.bsonserialize
         * @return array
         */
        public function bsonSerialize()
        {
            return $this->index;
        }
    }
