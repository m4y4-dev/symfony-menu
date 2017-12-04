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

use Symfony\Component\Security\Http\AccessMap;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Single Menu-Item
 *
 * @author Sven Siebrands <info@s-dl.biz>
 */
class MenuItem
{
	/**
	 * @var boolean
	 */
	public $active = false;

    /**
     * @var boolean
     */
    public $visible = true;

	/**
	 * @param string
	 */
	public $path;

	/**
	 * @var Request
	 */
	private $request;

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
	 * Constructor
	 * 
	 * @param Request                       $request
	 * @param AccessMap                     $accessMap
	 * @param Router                        $router
	 * @param AuthorizationCheckerInterface $authorizationChecker
	 * @param array                         $values
	 */
	public function __construct(Request $request, AccessMap $accessMap, Router $router, AuthorizationCheckerInterface $authorizationChecker, array $values)
	{
		$this->request = $request;
		$this->accessMap = $accessMap;
		$this->router = $router;
		$this->authorizationChecker = $authorizationChecker;

        $path = $values['path'];
        unset($values['path']);

        $this->createProperties($values);

		$this->path = $this->resolvePath($path);
		
		$this->checkAuthorization();
	}

	/**
	 * @param array $values
	 * 
	 * @return void
	 */
	private function createProperties(array $values)
	{
		if (isset($values['submenu'])) {
			$values['submenu'] = new $values['submenu']($this->request, $this->accessMap, $this->router, $this->authorizationChecker);
		}

		foreach ($values as $name => $value) {
			$this->{$name} = $value;
		}
	}

	/**
	 * User allowed to view this item?
	 * 
	 * @throws AccessDeniedException
	 * 
	 * @return void
	 */
	public function checkAuthorization()
	{
		$accessPattern = $this->accessMap->getPatterns(
			Request::create($this->path, 'GET')
		);

		foreach ($accessPattern[0] as $role) {
			if (false === $this->authorizationChecker->isGranted($role)) {
	            throw new AccessDeniedException(__CLASS__.':'.__METHOD__, null);
	        }
		}
	}

	/**
	 * Generate url from path and sets active if path is current url
	 * 
	 * @param string
	 * 
	 * @return string
	 */
	public function resolvePath($path)
	{
		try {
			$originalPath = $path;

            if ($this->visible) {
                $path = $this->router->generate($path);
            }

			if ($this->request->get('_route') === $originalPath) {
				$this->active = true;
			}
		} catch (RouteNotFoundException $e) {
			if ($this->request->getRequestUri() === $originalPath) {
				$this->active = true;
			}
		}

		return $this->visible ? $path : '';
	}
}

?>