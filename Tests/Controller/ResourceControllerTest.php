<?php

namespace Innmind\RestBundle\Tests\Controller;

use Innmind\RestBundle\Controller\ResourceController;
use Innmind\RestBundle\DependencyInjection\InnmindRestExtension;
use Innmind\RestBundle\DependencyInjection\Compiler\RegisterFormatPass;
use Innmind\RestBundle\DependencyInjection\Compiler\RegisterStoragePass;
use Innmind\RestBundle\DependencyInjection\Compiler\RegisterDefinitionCompilerPass;
use Innmind\Rest\Server\Resource;
use Innmind\Rest\Server\Collection;
use Innmind\Neo4j\ONM\Configuration;
use Innmind\Neo4j\ONM\EntityManagerFactory;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher;
use Symfony\Component\EventDispatcher\DependencyInjection\RegisterListenersPass;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Routing\Router;
use Symfony\Component\Routing\Route;
use Symfony\Component\Validator\Validator;
use Symfony\Component\Validator\Validation;
use Symfony\Bundle\FrameworkBundle\DependencyInjection\Compiler\SerializerPass;

class ResourceControllerTest extends \PHPUnit_Framework_TestCase
{
    protected $c;
    protected $b;
    protected $d;

    public function setUp()
    {
        $e = new InnmindRestExtension;
        $b = new ContainerBuilder;
        $loader = new Loader\YamlFileLoader(
            $b,
            new FileLocator(__DIR__.'/../../Resources/config')
        );
        $loader->load('services.yml');
        $conf = [
            'server' => [
                'collections' => [
                    'web' => [
                        'storage' => 'neo4j',
                        'resources' => [
                            'resource' => [
                                'id' => 'uuid',
                                'storage' => 'neo4j',
                                'properties' => [
                                    'uuid' => [
                                        'type' => 'string',
                                        'access' => ['READ'],
                                    ],
                                    'name' => [
                                        'type' => 'string',
                                        'access' => ['READ', 'UPDATE'],
                                    ],
                                ],
                                'options' => [
                                    'class' => Bar::class,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
        $e->load([$conf], $b);

        $conf = Configuration::create([
            'cache' => sys_get_temp_dir(),
            'reader' => 'yaml',
            'locations' => ['fixtures/neo4j'],
        ], true);
        $conn = [];

        if (!getenv('CI')) {
            $conn = [
                'host' => 'docker',
                'username' => 'neo4j',
                'password' => 'ci',
            ];
        }

        $em = EntityManagerFactory::make(
            $conn,
            $conf,
            new EventDispatcher
        );

        $b->setDefinition(
            'neo4j',
            $d = new DefinitionDecorator(
                'innmind_rest.server.storage.abstract.neo4j'
            )
        );
        $d->replaceArgument(0, $em);
        $d->addTag('innmind_rest.server.storage', ['name' => 'neo4j']);
        $b
            ->addCompilerPass(new RegisterDefinitionCompilerPass)
            ->addCompilerPass(new RegisterFormatPass)
            ->addCompilerPass(new RegisterStoragePass)
            ->addCompilerPass(new RegisterListenersPass);
        $b->setDefinition(
            'event_dispatcher',
            $ev = new Definition(ContainerAwareEventDispatcher::class)
        );
        $ev->addArgument($b);
        $b->setDefinition(
            'property_accessor',
            new Definition(PropertyAccessor::class)
        );
        $b->setDefinition(
            'router',
            $router = new Definition(Router::class)
        );
        $router->addArgument(new Reference('innmind_rest.route_loader'));
        $router->addArgument('.');
        $b->setDefinition('serializer', new Definition(Serializer::class));
        $b->setDefinition('validator', $v = new Definition(Validator::class));
        $v->setFactory([Validation::class, 'createValidator']);
        $b->compile();
        $this->b = $b;
        $this->d = $b
            ->get('innmind_rest.server.registry')
            ->getCollection('web')
            ->getResource('resource');
        $this->c = new ResourceController;
        $this->c->setContainer($b);
        $b->get('innmind_rest.route_loader')->load('.');
    }

    public function testCreateAction()
    {
        $resource = new Resource;
        $resource->setDefinition($this->d);
        $this->assertSame(
            $resource,
            $this->c->createAction($resource)
        );
        $this->assertTrue(is_string($resource->get('uuid')));
    }

    public function testGetAction()
    {
        $resource = new Resource;
        $resource->setDefinition($this->d);
        $this->c->createAction($resource);

        $r = $this->c->getAction($this->d, $resource->get('uuid'));

        $this->assertInstanceOf(
            Resource::class,
            $r
        );
    }

    public function testIndexAction()
    {
        $resource = new Resource;
        $resource->setDefinition($this->d);
        $this->c->createAction($resource);

        $resources = $this->c->indexAction($this->d);

        $this->assertInstanceOf(
            Collection::class,
            $resources
        );
        $this->assertTrue($resources->count() > 1);
    }

    public function testUpdateAction()
    {
        $resource = new Resource;
        $resource->setDefinition($this->d);
        $this->c->createAction($resource);
        $id = $resource->get('uuid');

        $resource = new Resource;
        $resource
            ->setDefinition($this->d)
            ->set('name', 'foo');
        $this->assertSame(
            $resource,
            $this->c->updateAction($resource, $id)
        );

        $resource = $this->c->getAction($this->d, $id);
        $this->assertSame(
            'foo',
            $resource->get('name')
        );
    }

    public function testDeleteAction()
    {
        $resource = new Resource;
        $resource->setDefinition($this->d);
        $this->c->createAction($resource);

        $this->assertSame(
            null,
            $this->c->deleteAction($this->d, $resource->get('uuid'))
        );
    }

    public function testOptionsAction()
    {
        $this->assertSame(
            [
                'resource' => [
                    'id' => 'uuid',
                    'properties' => [
                        'uuid' => [
                            'type' => 'string',
                            'access' => ['READ'],
                            'variants' => [],
                        ],
                        'name' => [
                            'type' => 'string',
                            'access' => ['READ', 'UPDATE'],
                            'variants' => [],
                        ],
                    ],
                ],
            ],
            $this->c->optionsAction($this->d)
        );
    }

    public function testCapabilitiesAction()
    {
        $this->assertTrue(isset($this->c->capabilitiesAction()['innmind_rest_web_resource_options']));
        $this->assertInstanceOf(
            Route::class,
            $this->c->capabilitiesAction()['innmind_rest_web_resource_options']
        );
    }

    /**
     * @expectedException Innmind\Rest\Server\Exception\ResourceNotFoundException
     */
    public function testThrowIfResourceNotFound()
    {
        $this->c->getAction($this->d, 'foo');
    }
}

class Bar
{
    public $uuid;
    public $name;
}
