<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Messenger\Bridge\Mongo\Transport;

use Doctrine\ODM\MongoDB\DocumentManager;
use MongoDB\Client;
use Symfony\Component\Messenger\Exception\InvalidArgumentException;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Symfony\Component\Messenger\Transport\TransportFactoryInterface;
use Symfony\Component\Messenger\Transport\TransportInterface;

final class MongoTransportFactory implements TransportFactoryInterface
{
    private const DEFAULT_OPTIONS = [
        'collection_name' => 'messages_queue',
        'queue_name' => 'default',
        'redeliver_timeout' => 3600,
    ];

    /**
     * @var Client
     */
    private $client;

    public function __construct(
        DocumentManager $dm
    ) {
        $this->client = $dm->getClient();
    }

    /**
     * @throws InvalidArgumentException
     */
    public function createTransport(
        string $dsn,
        array $options,
        SerializerInterface $serializer
    ): TransportInterface {
        $options = array_replace_recursive(self::DEFAULT_OPTIONS, $options);

        if (!isset($options['database_name'])) {
            throw new InvalidArgumentException('Missing required options "database_name".');
        }

        $collection = $this->client->selectCollection(
            $options['database_name'],
            $options['collection_name']
        );

        return new MongoTransport($collection, $serializer, $options);
    }

    public function supports(string $dsn, array $options): bool
    {
        return 0 === strpos($dsn, 'mongo://');
    }
}
