<?php

namespace Innmind\RestBundle\Tests\Controller;

use Innmind\RestBundle\Controller\ResourceController;
use Innmind\RestBundle\DependencyInjection\InnmindRestExtension;
use Innmind\Rest\Server\DependencyInjection\Compiler\RegisterFormatPass;
use Innmind\Rest\Server\DependencyInjection\Compiler\RegisterStoragePass;
use Innmind\Rest\Server\DependencyInjection\Compiler\RegisterDefinitionCompilerPass;
use Innmind\Rest\Server\HttpResource;
use Innmind\Rest\Server\Collection;
use Innmind\Rest\Server\Definition\Property;
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
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\DependencyInjection\Compiler\SerializerPass;
use Psr\Log\NullLogger;

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
                                        'options' => [
                                            'optional' => null,
                                        ],
                                    ],
                                ],
                                'options' => [
                                    'class' => Bar::class,
                                ],
                            ],
                            'priv' => [
                                'id' => 'uuid',
                                'storage' => 'neo4j',
                                'properties' => [
                                    'uuid' => [
                                        'type' => 'string',
                                        'access' => ['READ'],
                                    ],
                                ],
                                'options' => [
                                    'private' => null,
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
        $d->addTag('innmind_rest.server.storage', ['alias' => 'neo4j']);
        $b
            ->addCompilerPass(new RegisterDefinitionCompilerPass(
                'innmind_rest.server.definition_compiler',
                'innmind_rest.server.definition_pass'
            ))
            ->addCompilerPass(new RegisterFormatPass(
                'innmind_rest.server.formats',
                'innmind_rest.server.format'
            ))
            ->addCompilerPass(new RegisterStoragePass(
                'innmind_rest.server.storages',
                'innmind_rest.server.storage'
            ))
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
        $router->addArgument(new Reference('innmind_rest.server.route_loader'));
        $router->addArgument('.');
        $b->setDefinition('serializer', new Definition(Serializer::class));
        $b->setDefinition('validator', $v = new Definition(Validator::class));
        $b->setDefinition('request_stack', new Definition(RequestStack::class));
        $b->setDefinition('logger', new Definition(NullLogger::class));
        $v->setFactory([Validation::class, 'createValidator']);
        $b->setParameter('kernel.cache_dir', sys_get_temp_dir());
        $b->compile();
        $this->b = $b;
        $this->d = $b
            ->get('innmind_rest.server.registry')
            ->getCollection('web')
            ->getResource('resource');
        $this->c = new ResourceController(
            $b->get('innmind_rest.server.storages'),
            $b->get('router'),
            $b->get('innmind_rest.server.registry')
        );
        $b->get('request_stack')->push(new Request);
    }

    public function testCreateAction()
    {
        $resource = new HttpResource;
        $resource->setDefinition($this->d);
        $this->assertSame(
            $resource,
            $this->c->createAction($resource)
        );
        $this->assertTrue(is_string($resource->get('uuid')));
    }

    public function testGetAction()
    {
        $resource = new HttpResource;
        $resource->setDefinition($this->d);
        $this->c->createAction($resource);

        $r = $this->c->getAction($this->d, $resource->get('uuid'));

        $this->assertInstanceOf(
            HttpResource::class,
            $r
        );
    }

    public function testIndexAction()
    {
        $resource = new HttpResource;
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
        $resource = new HttpResource;
        $resource->setDefinition($this->d);
        $this->c->createAction($resource);
        $id = $resource->get('uuid');

        $resource = new HttpResource;
        $resource
            ->setDefinition($this->d)
            ->set('name', 'foo');
        $updated = $this->c->updateAction($resource, $id);
        $this->assertSame(
            $id,
            $updated->get('uuid')
        );
        $this->assertSame(
            'foo',
            $updated->get('name')
        );

        $resource = $this->c->getAction($this->d, $id);
        $this->assertSame(
            'foo',
            $resource->get('name')
        );
    }

    public function testDeleteAction()
    {
        $resource = new HttpResource;
        $resource->setDefinition($this->d);
        $this->c->createAction($resource);

        $this->assertSame(
            null,
            $this->c->deleteAction($this->d, $resource->get('uuid'))
        );
    }

    public function testOptionsAction()
    {
        $this->d->addProperty(
            (new Property('foo'))
                ->setType('array')
                ->addAccess('READ')
                ->addOption('inner_type', 'string')
        );
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
                            'optional' => true,
                        ],
                        'foo' => [
                            'type' => 'array',
                            'access' => ['READ'],
                            'variants' => [],
                            'inner_type' => 'string',
                        ],
                    ],
                ],
            ],
            $this->c->optionsAction($this->d)
        );
    }

    public function testCapabilitiesAction()
    {
        $caps = $this->c->capabilitiesAction();
        $this->assertSame(
            1,
            count($caps)
        );
        $this->assertTrue(isset($caps['innmind_rest_web_resource_options']));
        $this->assertInstanceOf(
            Route::class,
            $caps['innmind_rest_web_resource_options']
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
