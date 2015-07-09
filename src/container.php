<?php
namespace Ion;

class Container
{
	private $keys = array();
	private $closures = array();
	private $factories = array();
	private $instances = array();
	private $interfaces = array();
	private $params = array();
	private $args = array();

	public function make($class, array $args = array())
	{
		$key = ($args) ? $class . '_' . crc32(serialize($args)) : $class;
		$this->args = $args;
		if (isset($this->keys[$key]))
		{
			return $this->_get($key);
		}
		$instance = $this->_createInstance($class, $args);				
		$this->keys[$key] = true;
		$this->instances[$key] = $instance;
		return $instance;
	}

	public function makeNew($class, array $args = array())
	{
		$this->args = $args;
		if (isset($this->factories[$class]))
		{
			return $this->closures[$class]($this);
		}
		$key = ($args) ? $class . '_' . crc32(serialize($args)) : $class;
		if (isset($this->instances[$key]))
		{
			return clone $this->instances[$key];
		}
		$instance = $this->_createInstance($class, $args);
		return $instance;
	}

	private function _createInstance($class, array $args)
	{
		$reflection = new \ReflectionClass($class);
		$constructor = $reflection->getConstructor();
		if (!$constructor || !$num_of_params = $constructor->getNumberOfParameters()) {
			$this->keys[$class] = TRUE;
			$instance = $this->instances[$class] = new $class;
			return $instance;
		}

		$predefined_args = array();
		$default_args = $this->_extractParams($class, $constructor);
		foreach ($default_args['param'][$class] as $k => $v) {
			$predefined_args[$k] = $v;
		}		
		foreach ($default_args['class'][$class] as $k => $v) {
			$predefined_args[$k] = $this->make($v);
		}
		foreach ($predefined_args as $pos => $v) {
			array_splice($args, $pos, 0, array($v));
		}
		if (count($args) != $num_of_params) {
			throw new \RuntimeException(sprintf('Your supplied arguments are less than required. Required: %s, given: %s', $num_of_params, count($args)));
		}
		$instance = $reflection->newInstanceArgs($args);
		return $instance;
	}

	private function _extractParams($key, \ReflectionMethod $method)
	{
		$params = $method->getParameters();
		$args = array();
		$args['class'][$key] = array();
		$args['param'][$key] = array();
		foreach ($params as $k => $param) {
			$param_name = $param->getName();
				if ($type_hint = $param->getClass()) {
				$class_name = $type_hint->getName();
				if (isset($this->keys[$class_name]) || class_exists($class_name))
				{
					$args['class'][$key][$k] = $class_name;
					continue;
				}
				else
				{
					throw new \Exception(sprintf("Invalid parameter. The class '%s' may not exist", $class_name));
				}
			}
			elseif (isset($this->params[$param_name]))
			{
				$args['param'][$key][$k] = $this->params[$param_name];
				continue;
			}
		}
		return $args;
	}

	public function register($key, $closure)
	{
		if (!$closure instanceOf $closure) {
			throw new \InvalidArgumentException(sprintf('The $closure must be a closure, given: %s', gettype($closure)));
		}
		$this->keys[$key] = TRUE;
		$this->closures[$key] = $closure;
	}

	public function registerFactory($key, $closure)
	{
		$this->register($key, $closure);
		$this->factories[$key] = TRUE;
	}

	public function bindInterface($interface, $class)
	{
		$this->keys[$interface] = TRUE;
		if (isset($this->keys[$class]))
		{
			$this->interfaces[$interface] = $class;
		}
		elseif (class_exists($class))
		{
			$reflection = new \ReflectionClass($class);
			if (!$reflection->implementsInterface($interface)) {
				throw new \RuntimeException(sprintf("The interface %s is not implemented by class %s.", $interface, $class));
			}
			$constructor = $reflection->getConstructor();
			if (!$constructor || !$constructor->getNumberOfParameters()) {
				$this->keys[$class] = TRUE;
				$this->instances[$class] = new $class;
				$this->interfaces[$interface] = $class;
			}
			else
			{
				throw new \BadMethodCallException("The class constructor have parameters, instantiate it first or just specify the key");
			}
		}
		else
		{
			unset($this->keys[$interface]);
			throw new \InvalidArgumentException(sprintf("Invalid argument two. The registered key or class name expected and the class must implement that interface, given class: %s, interface: %s", $class, $interface));
		}
	}

	public function setParam($param, $value)
	{
		$this->params[$param] = $value;
	}

	public function param($param)
	{
		if (!isset($this->params[$param])) {
			throw new \RuntimeException(sprintf('Parameter %s not found. Please register it first via setParam($param, $value).', $param));
		}
		return $this->params[$param];
	}

	public function getArgs()
	{
		return $this->args();
	}

	private function _get($key)
	{
		if (!isset($this->keys[$key])) {
			throw new \BadMethodCallException(sprintf('Key %s is not registered', $key));
		}
		if (isset($this->factories[$key])) {
			return $this->closures[$key]($this);
		}
		if (isset($this->instances[$key])) {
			return $this->instances[$key];
		}
		if (isset($this->interfaces[$key])) {
			return $this->_get($this->interfaces[$key]);
		}
		$instance = $this->instances[$key] = $this->closures[$key]($this);
		return $instance;
	}
}