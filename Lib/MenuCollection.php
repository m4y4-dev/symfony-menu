<?php

/*
 * This file is part of the s-dl/Symfony/Menu package.
 *
 * (c) Sven Siebrands <info@s-dl.biz>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Menu;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Http\AccessMap;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Debug\Exception\ClassNotFoundException;

/**
 * Collection of single Menu-Items
 *
 * @author Sven Siebrands <info@s-dl.biz>
 */
class MenuCollection implements \Iterator
{
	/**
	 * @var array
	 */
	protected $menus = [];

	/**
	 * @var RequestStack
	 */
	private $requestStack;

	/**
	 * @var AccessMap
	 */
	private $accessMap;

	/**
	 * @var Router
	 */
	private $router;

	/**
	 * @var AuthorizationCheckerInterface
	 */
	private $authorizationChecker;

    /**
     * @var string
     */
    private $menuNamespace;

	/**
	 * Constructor
	 * 
	 * @param RequestStack                  $requestStack
	 * @param AccessMap                     $accessMap
	 * @param Router                        $router
	 * @param AuthorizationCheckerInterface $authorizationChecker
     * @param string                        $menuNamespace
	 */
	public function __construct(RequestStack $requestStack, AccessMap $accessMap, Router $router, AuthorizationCheckerInterface $authorizationChecker, $menuNamespace)
	{
		$this->requestStack = $requestStack;
		$this->accessMap = $accessMap;
		$this->router = $router;
        $this->authorizationChecker = $authorizationChecker;
		$this->menuNamespace = str_replace("/", "\\", $menuNamespace);
	}

	/**
	 * @param string $name
	 * 
	 * @return mixed
	 */
	public function __get($name)
	{
		return $this->menus[$name];
	}

	/**
	 * @param string $name
	 * @param array  $arguments
	 * 
	 * @throws BadMethodCallException
	 * 
	 * @return mixed
	 */
	public function __call($name, $arguments)
	{
		if (isset($this->menus[$name])) {
			return $this->menus[$name];
		}

		try {
			$this->add($name, $this->menuNamespace.'\\'.$name);

			return $this->menus[$name];
		} catch(ClassNotFoundException $e) {}

		throw new \BadMethodCallException('Method not found: '.$name.' in '.__NAMESPACE__.__CLASS__);
	}

	/**
	 * @return boolean
	 */
	public function valid()
	{
		return null !== key($this->menus);
	}

	/**
	 * @return void
	 */
	public function rewind()
	{
		reset($this->menus);
	}

	/**
	 * @return void
	 */
	public function next()
	{
		next($this->menus);
	}

	/**
	 * @return mixed
	 */
	public function current()
	{
		return current($this->menus);
	}

	/**
	 * @return mixed
	 */
	public function key()
	{
		return key($this->menus);
	}

	/**
	 * @param mixed  $identifier
	 * @param string $menu
	 * 
	 * @return MenuCollection
	 */
	public function add($identifier, $menu)
	{
		$menu = new $menu($this->requestStack->getCurrentRequest(), $this->accessMap, $this->router, $this->authorizationChecker);

		$this->menus[$identifier] = $menu;

		return $this;
	}

	/**
	 * @param mixed $identifier
	 * 
	 * @return MenuCollection
	 */
	public function remove($identifier)
	{
		if (isset($this->menus[$identifier])) {
			unset($this->menus[$identifier]);
		}

		return $this;
	}

	/**
	 * @return array
	 */
	public function toArray()
	{
		return $this->menus;
	}
}

?>