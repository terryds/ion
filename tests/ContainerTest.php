<?php

class ContainerTest extends PHPUnit_Framework_TestCase
{
	private $ion;

	public function setUp()
	{
		$this->ion = new Ion\Container;
	}

	public function tearDown()
	{
		$this->ion = null;
	}

	public function testInternalClass()
	{
		$directory_iterator = $this->ion->make('DirectoryIterator',array('.'));
		$this->assertInstanceOf('DirectoryIterator', $directory_iterator);
	}

	public function testNoConstructor()
	{
		$no_constructor = $this->ion->make('NoConstructor');
		$this->assertInstanceOf('NoConstructor', $no_constructor);
	}

	public function testConstructorNoParam()
	{
		$no_constructor_param = $this->ion->make('NoConstructorParam');
		$this->assertInstanceOf('NoConstructorParam', $no_constructor_param);
	}

	public function testNamespacedClass()
	{
		$namespaced = $this->ion->make('Bar\A');
		$this->assertInstanceOf('Bar\A', $namespaced);
	}

	/**
	 * @expectedException RuntimeException
	 */
	public function testLackOfArgument()
	{
		$need_an_argument = $this->ion->make('NeedAnArgument');
	}

	public function testRegisterClass()
	{
		$config = array('Foo','Bar','Baz');
		$this->ion->register('ToBeRegisteredWithThreeArgs', function() use ($config)
		{
			return new ToBeRegisteredWithThreeArgs($config[0], $config[1], $config[2]);
		});
		$toBeRegisteredWithThreeArgs = $this->ion->make('ToBeRegisteredWithThreeArgs');
		$this->assertEquals($config[0], $toBeRegisteredWithThreeArgs->a);
		$this->assertEquals($config[1], $toBeRegisteredWithThreeArgs->b);
		$this->assertEquals($config[2], $toBeRegisteredWithThreeArgs->c);
	}

	public function testRegisterAFactoryWithoutArgs()
	{
		$this->ion->registerFactory('ArrayObject', function() {
			return new ArrayObject;
		});
		$arrayObject1 = $this->ion->make('ArrayObject');
		$arrayObject2 = $this->ion->make('ArrayObject');
		$this->assertNotSame($arrayObject2, $arrayObject1);
	}

	public function testRegisterAFactoryWithArgs()
	{
		$this->ion->registerFactory('User', function($c) {
			return new User($c->getArgs());
		});
		$user1 = $this->ion->make('User',array('Foo'));
		$this->assertInstanceOf('User', $user1);
		$this->assertEquals('Foo', $user1->name);
		$user2 = $this->ion->make('User', array('Bar'));
		$this->assertNotSame($user2, $user1);
	}

	public function testBindInterfaceToASharedInstance()
	{
		$this->ion->register('ArrayObject', function() {
			return new ArrayObject;
		});
		$this->ion->bindInterface('ArrayAccess', 'ArrayObject');
		$arrayObject1 = $this->ion->make('ArrayAccess');
		$this->assertInstanceOf('ArrayObject', $arrayObject1);
		$arrayObject2 = $this->ion->make('ArrayAccess');
		$this->assertSame($arrayObject1, $arrayObject2);
	}

	public function testBindInterfaceToAFactory()
	{
		$this->ion->registerFactory('ArrayObject', function() {
			return new ArrayObject;
		});
		$this->ion->bindInterface('ArrayAccess', 'ArrayObject');
		$arrayObject1 = $this->ion->make('ArrayAccess');
		$this->assertInstanceOf('ArrayObject', $arrayObject1);
		$arrayObject2 = $this->ion->make('ArrayAccess');
		$this->assertNotSame($arrayObject1, $arrayObject2);
	}

	public function testRegisterWithExternalResource()
	{
		$external = array();
		$this->ion->register('ToBeRegisteredWithExternalResource', function() use ($external) {
			return new ToBeRegisteredWithExternalResource($external);
		});

		$object = $this->ion->make('ToBeRegisteredWithExternalResource');
		$this->assertSame($external, $object->internal);
	}

	public function testForceANewInstance()
	{
		$this->ion->register('stdClass', function() {
			return new stdClass;
		});
		$arrayObject1 = $this->ion->makeNew('stdClass');
		$arrayObject2 = $this->ion->makeNew('stdClass');
		$this->assertNotSame($arrayObject2, $arrayObject1);
	}

	public function testInstantiateAdvancedClass()
	{
		$this->ion->register('E', function() {
			return new E('bom','bar');
		});
		$this->ion->bindInterface('MyInterface','MyClass');
		$a = $this->ion->make('Long',array('This is a Message', 'USER'));
		$this->assertInstanceOf('Long', $a);
	}
}