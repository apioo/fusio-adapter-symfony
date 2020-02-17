Fusio-Adapter-Symfony
=====

[Fusio] adapter which helps to integrate features of Symfony. You can install
the adapter with the following steps inside your Fusio project:

    composer require fusio/adapter-symfony
    php bin/fusio system:register "Fusio\Adapter\Symfony\Adapter"

[Fusio]: https://www.fusio-project.org/

## Configuration

All your entities needs to be placed inside the folder `src/Entity` since the
Doctrine connection checks only this folder.

## Example

Through the Doctrine connection you can build API endpoints using the Doctrine
ORM i.e.:

```php
<?php

namespace App\Action;

use Fusio\Engine\ActionAbstract;
use Fusio\Engine\ContextInterface;
use Fusio\Engine\ParametersInterface;
use Fusio\Engine\RequestInterface;
use JMS\Serializer\ArrayTransformerInterface;
use JMS\Serializer\SerializerBuilder;

class Messages extends ActionAbstract
{
    public function handle(RequestInterface $request, ParametersInterface $configuration, ContextInterface $context)
    {
        $entityManager = $this->connector->getConnection('doctrine');

        /** @var ArrayTransformerInterface $serializer */
        $serializer = SerializerBuilder::create()->build();

        $dql = "SELECT m FROM App\Entity\Message m ORDER BY m.id DESC";

        $query = $entityManager->createQuery($dql);
        $messages = $query->getResult();

        $result = [];
        foreach ($messages as $message) {
            $result[] = $serializer->toArray($message);
        }

        return $this->response->build(200, [], [
            'todos' => $result,
        ]);
    }
}
```
