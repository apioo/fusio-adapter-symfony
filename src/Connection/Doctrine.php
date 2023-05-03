<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2022 Christoph Kappestein <christoph.kappestein@gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Fusio\Adapter\Symfony\Connection;

use Doctrine\Common\Cache\PhpFileCache;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Doctrine\ORM\Tools\Setup;
use Fusio\Engine\ConnectionInterface;
use Fusio\Engine\Form\BuilderInterface;
use Fusio\Engine\Form\ElementFactoryInterface;
use Fusio\Engine\ParametersInterface;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Cache\Adapter\Psr16Adapter;

/**
 * Doctrine
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org/
 */
class Doctrine implements ConnectionInterface
{
    private Connection $connection;
    private CacheInterface $cache;

    public function __construct(Connection $connection, CacheInterface $cache)
    {
        $this->connection = $connection;
        $this->cache = $cache;
    }

    public function getName(): string
    {
        return 'Doctrine';
    }

    public function getConnection(ParametersInterface $config): EntityManager
    {
        $paths = $config->get('paths');
        $isDevMode = $config->get('mode') === 'dev';

        $configuration = ORMSetup::createAttributeMetadataConfiguration($paths, $isDevMode, null, new Psr16Adapter($this->cache));

        return new EntityManager($this->connection, $configuration);
    }

    public function configure(BuilderInterface $builder, ElementFactoryInterface $elementFactory): void
    {
        $builder->add($elementFactory->newCollection('paths', 'Path', 'text', 'Entity folder'));
        $builder->add($elementFactory->newSelect('mode', 'Mode', ['dev' => 'Development', 'prod' => 'Production'], 'Setup the entity manager in development or production mode'));
    }
}
