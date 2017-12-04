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
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Menu
 *
 * @author Sven Siebrands <info@s-dl.biz>
 */
class Menu extends \ArrayObject
{
	/**
	 * @var boolean
	 */
	public $hasActiveItem = false;

    /**
     * @var boolean
     */
    public $hasVisibleItem = false;

	/**
	 * @var array
	 * 
	 * 		[
	 * 			[
	 * 				'name' => 'item 1',
	 * 				'path' => 'some_location'
	 * 			],
	 * 			[
	 * 				'name' => 'item 2',
	 * 				'link' => 'http://example.com'
	 * 			],
	 * 			...
	 * 		]
	 */
	protected $items = [];

	/**
	 * Constructor
     * 
     * Converts items into MenuItems
	 * 
	 * @param Request                       $request
	 * @param AccessMap                     $accessMap
	 * @param Router                        $router
	 * @param AuthorizationCheckerInterface $authorizationChecker
	 */
	public function __construct(Request $request, AccessMap $accessMap, Router $router, AuthorizationCheckerInterface $authorizationChecker)
	{
		foreach ($this->items as $key => $value) {
			try {
				$this->items[$key] = new MenuItem($request, $accessMap, $router, $authorizationChecker, $value);

				if ($this->items[$key]->active) {
					$this->hasActiveItem = true;
				}
                if ($this->items[$key]->visible) {
                    $this->hasVisibleItem = true;
                }
			} catch (AccessDeniedException $e) {
				unset($this->items[$key]);
			}
		}

		parent::__construct($this->items);
	}
}

?>