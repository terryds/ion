<?php

class NoConstructor
{

}

class NoConstructorParam
{
	public function __construct() {
		
	}
}

class NeedAnArgument
{
	public function __construct($a) {

	}
}

class ToBeRegisteredWithThreeArgs
{
	public function __construct($a, $b, $c) {
		$this->a = $a;
		$this->b = $b;
		$this->c = $c;
	}
}

class ToBeRegisteredWithExternalResource
{
	public function __construct($external) {
		$this->internal = $external;
	}
}

class User
{
	public function __construct($name) {
		$this->name = $name;
	}
}

class Long
{
	public function __construct(B $b, MyInterface $myclass, $message, $user)
	{
		$this->b = $b;
		$this->message = "Hiiii $message from $user";
	}
}

class MyClass implements MyInterface
{
	
}

interface MyInterface
{

}

class B
{
	public function __construct(C $c)
	{
		$this->c = $c;
	}
}

class C
{
	public function __construct(E $e)
	{
		
	}
}

class E
{
	public function __construct($name, $boom)
	{
		
	}
}