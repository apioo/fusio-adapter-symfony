<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2020 Christoph Kappestein <christoph.kappestein@gmail.com>
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
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;
use Fusio\Engine\ConnectionInterface;
use Fusio\Engine\Form\BuilderInterface;
use Fusio\Engine\Form\ElementFactoryInterface;
use Fusio\Engine\ParametersInterface;

/**
 * Doctrine
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Doctrine implements ConnectionInterface
{
    public function getName()
    {
        return 'Doctrine';
    }

    /**
     * @param \Fusio\Engine\ParametersInterface $config
     * @return \Doctrine\ORM\EntityManager
     */
    public function getConnection(ParametersInterface $config)
    {
        if ($config->get('mode') === 'dev') {
            $configuration = Setup::createAnnotationMetadataConfiguration(
                [PSX_PATH_SRC . '/Entity'],
                true,
                null,
                null,
                false
            );
        } else {
            $configuration = Setup::createAnnotationMetadataConfiguration(
                [PSX_PATH_SRC . '/Entity'],
                false,
                PSX_PATH_CACHE . '/doctrine/proxy',
                new PhpFileCache(PSX_PATH_CACHE . '/doctrine/cache'),
                false
            );
        }

        $connection =  [
            'dbname' => $config->get('database'),
            'user' => $config->get('username'),
            'password' => $config->get('password'),
            'host' => $config->get('host'),
            'driver' => $config->get('driver') ?: 'pdo_mysql',
        ];

        return EntityManager::create($connection, $configuration);
    }

    public function configure(BuilderInterface $builder, ElementFactoryInterface $elementFactory)
    {
        $drivers = ['pdo_mysql', 'mysqli', 'pdo_pgsql', 'pdo_sqlsrv', 'sqlsrv', 'oci8', 'sqlanywhere'];
        $drivers = array_combine($drivers, $drivers);

        $builder->add($elementFactory->newSelect('driver', 'Driver', $drivers, 'Doctrine driver'));
        $builder->add($elementFactory->newInput('host', 'Host', 'text', 'The database host'));
        $builder->add($elementFactory->newInput('database', 'Database', 'text', 'The database name'));
        $builder->add($elementFactory->newInput('username', 'Username', 'text', 'The database username'));
        $builder->add($elementFactory->newInput('password', 'Password', 'text', 'The database password'));
        $builder->add($elementFactory->newSelect('mode', 'Mode', ['dev' => 'Development', 'prod' => 'Production'], 'Setup the entity manager in development or production mode'));
    }
}
