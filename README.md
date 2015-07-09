# ION Container
A minimalist inversion of control container using automatic dependency injection

## Installation

To install __ION Container__, you need to add it to your composer.json file
```
{
      "require": "terrydjony/ion"
}
```
Or, by using terminal
```
composer require terrydjony/ion
```

## Usage
First, you need to instantiate the container
```php
use Ion\Container;

$ion = new Container();
```

## Instantiating Object

To instantiate any objects, you should use `make($class, array $args = array())`, instead of `new` keyword

Notice that the parameter two `$args` is optional
For example, if you want to instantiate `stdClass` class.
```php
$stdClass = $ion->make('stdClass');

// $stdClass is an instance of stdClass
```

##Registering Service

You can register the way to instantiate some class via `register($key, $closure)`
Here is an example of registering PDO class with some defined arguments
```php
$ion->register('PDO', function() {
    return new PDO('mysql:host=localhost;dbname=databasename','root','');
};

$pdo = $ion->make('PDO');

// $pdo is an instance of PDO class with the arguments as defined before
```
The above example will register PDO service. So, if you instantiate it via `$ion->make('PDO')`, you don't need to supply any arguments since all the arguments have been defined before.

However, it will be nice to have a database config.
```php
$config = array('dsn'=>'mysql:host=localhost;dbname=databasename','username'=>'root','password'=>'');

$ion->register('PDO', function() use ($config) {
    return new PDO($config['dsn'],$config['username'],$username['password']);
});

$pdo = $ion->make('PDO');
And, notice that the first parameter of register is a key. The key can be anything, but it's recommended to use the class name as the key.

$ion->register('DAL', function() {
    return new PDO($config['dsn'],$config['username'],$config['password']);
};

$dal = $ion->make('DAL');
// $dal is a PDO object
```
Notice that if you register a service, it'll be a shared instance. If you want to register a factory, use registerFactory
## Registering Factory Services
You can register a factory using `registerFactory($key, $closure)`
```php
$ion->registerFactory('CarFactory', function() {
    return new Car();
};

$car1 = $ion->make('CarFactory');
$car2 = $ion->make('CarFactory');

// $car1 and $car2 are different objects
If you want to use the supplied arguments, you can use getArgs() method.

$this->ion->registerFactory('User', function($ion) {
  return new User($ion->getArgs());
});

$user1 = $this->ion->make('User',array('Foo'));
// $user1 is an instance with argument Foo
```
##Registering Parameters
You can also register parameters using `setParam($param, $value)` and get the value via `param($param_name)`
```php
$ion->setParam('db_dsn','mysql:host=localhost;dbname=databasename');
$ion->setParam('db_user','root');
$ion->setParam('db_password','');

$ion->register('PDO', function($ion) {
    return new PDO($ion->param('db_dsn'), $ion->param('db_user'), $ion->param('db_pass');
}
```
##Forcing New Instance
You can force a new instance using `makeNew($key, $args=array())`.
```php
// register a PDO service before

$pdo1 = $ion->makeNew('PDO');
$pdo2 = $ion->makeNew('PDO');

// $pdo1 and $pdo2 are different objects no matter how the PDO service is registered 
```  
##Defining Default Class For Interface
You can bind an interface to a class. So, if a class depends on that interface, that class will represent that interface

If the class must be instantiated with some arguments, you should register it first. Then, you give the key as the parameter two of bindInterface

But, if the class can be instantiated with no arguments, you can just specify the class name.
```php
class A
{
    public function __construct(DatabaseAdapterInterface $dba, StandardClassInterface $stdClass, StandardInterface $obj)
    {

    }
}

$ion->register('PDO', function() {
    return new PDO('mysql:host=localhost;dbname=databasename','root','');
}

// Binds DatabaseAdapterInterface to PDO class which has been registered
$ion->bindInterface('DatabaseAdapterInterface', 'PDO');

// Binds StandardClassInterface to stdClass which needs no arguments for construction
$ion->bindInterface('StandardClassInterface', 'stdClass');

$a = $ion->make('A');
```

##Automatic Dependency Injection

Suppose you want to instantiate a class A that needs class B which needs class C

I mean something like
```php
class A
{
  public function __construct(B $b)
  {

  }
}

class B
{
  public function __construct(C $c)
  {

  }
}

class C
{
  public function __construct()
  {
    
  }
}
```
And, to make a class A instance, you just need to:
```php
$a = $ion->make('A');
```
And, voila! You get the class A instance!

If you have a dependency that needs some parameters, you can register the dependency first before making object that needs that dependency

And, if the class needs some arguments, you can supply an array of arguments as parameter two of make or makeNew method.
```php
class A
{
    public function __construct(B $b, $name)
    {
        echo "Hello ".$name
    }
}

class B
{
    public function __construct(DatabaseAdapterInterface $dba)
    {

    }
}


$ion->register('PDO', function() {
    return new PDO('mysql:host=localhost;dbname=databasename','root','');
});
$ion->bindInterface('DatabaseAdapterInterface','PDO');

$a = $ion->make('A',array('World'));
```
The class A instance is made automatically.


