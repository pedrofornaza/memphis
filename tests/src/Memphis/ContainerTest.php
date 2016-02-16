<?php

namespace Memphis;

use ArrayIterator;
use Iterator;
use Doubles\ArrayIteratorWrapper;
use Doubles\IteratorWrapper;
use Doubles\ClassWithUnresolvableParameter;
use Memphis\Exception\BindResolving as BindResolvingException;
use Memphis\Exception\ClassConstructorResolving as ClassConstructorResolvingException;
use Memphis\Exception\ClassNotExists as ClassNotExistsException;

class ContainerTest extends \PHPUnit_Framework_TestCase
{
    public function test_container_can_bind_interface_to_class()
    {
        $container = new Container;

        $container->bind(Iterator::class, ArrayIterator::class);

        $instance = $container->get(Iterator::class);

        $this->assertInstanceOf(ArrayIterator::class, $instance);
    }

    public function test_container_can_bind_interface_to_instance()
    {
        $container = new Container;

        $arrayIterator = new ArrayIterator();
        $container->bind(Iterator::class, $arrayIterator);

        $instance = $container->get(Iterator::class);

        $this->assertSame($arrayIterator, $instance);
    }

    public function test_container_can_bind_interface_to_factory_closure()
    {
        $container = new Container;

        $container->bind(Iterator::class, function() {
            return new ArrayIterator();
        });

        $instance = $container->get(Iterator::class);

        $this->assertInstanceOf(ArrayIterator::class, $instance);
    }

    public function test_container_can_bind_interface_to_callable()
    {
        $container = new Container;

        $container->bind(Iterator::class, [$this, 'getArrayIterator']);

        $instance = $container->get(Iterator::class);

        $this->assertInstanceOf(ArrayIterator::class, $instance);
    }

    /**
     * Used as callable in test_container_can_bind_interface_to_callable
     */
    public function getArrayIterator()
    {
        return new ArrayIterator;
    }

    public function test_container_throw_exception_if_bind_cannot_be_resolved()
    {
        $this->setExpectedException(BindResolvingException::class);

        $container = new Container;

        $container->bind(Iterator::class, 'cannot resolve this');

        $container->get(Iterator::class);
    }

    public function test_container_can_make_any_instanciable_class_without_bind()
    {
        $container = new Container;
        $instance = $container->get(ArrayIterator::class);

        $this->assertInstanceOf(ArrayIterator::class, $instance);
    }

    public function test_container_throws_exception_if_class_does_not_exists()
    {
        $this->setExpectedException(ClassNotExistsException::class);

        $container = new Container;
        $container->get('MyClass');
    }

    public function test_container_resolves_constructor_parameters_recursively()
    {
        $container = new Container;

        $instance = $container->get(ArrayIteratorWrapper::class);

        $this->assertInstanceOf(ArrayIteratorWrapper::class, $instance);
        $this->assertInstanceOf(ArrayIterator::class, $instance->getIterator());
    }

    public function test_container_throws_exception_if_cannot_resolves_constructor_parameter()
    {
        $this->setExpectedException(ClassConstructorResolvingException::class);

        $container = new Container;
        $instance = $container->get(ClassWithUnresolvableParameter::class);
    }

    public function test_container_resolves_dependencies_with_binds()
    {
        $container = new Container;

        $container->bind(Iterator::class, ArrayIterator::class);

        $instance = $container->get(IteratorWrapper::class);

        $this->assertInstanceOf(IteratorWrapper::class, $instance);
        $this->assertInstanceOf(ArrayIterator::class, $instance->getIterator());
    }
}
