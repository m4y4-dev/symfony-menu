# symfony-menu
Simple way to use access-restricted menu in a symfony application


## Use with composer:

### Add to composer.json

```yml
"repositories": [
    {
        "type": "vcs",
        "url": "https://github.com/m4y4-dev/symfony-menu.git"
    }
]
```

### Run:

```
composer require m4y4-dev/symfony-menu:^1.0
```

## Add to your symfony application

### Add to app/service.yml
(only required if autoconfigure = false)

```yml
menu_collection:
    class: Symfony\Menu\MenuCollection
    arguments: ["@request_stack", "@security.access_map", "@router", "@security.authorization_checker", "AppBundle/Menu"]
```

### Make the menus available in twig by adding service to twig globals in app/config.yml

Example:
```yml
twig:
    debug: '%kernel.debug%'
    strict_variables: '%kernel.debug%'
    globals:
        menus: "@menu_collection"
```

### Add your menus to src/{BundleName}/Menu/

Example:
```php
<?php

namespace AppBundle\Menu;

use Symfony\Menu\Menu;

class MainMenu extends Menu
{
    protected $items = [
        [
            'name' => 'Dashboard',
            'icon' => '<i class="icon-home"></i>',
            'path' => 'homepage',
        ],
        [ 
            'name' => 'Link x',
            'icon' => '<i class="icon-diamond"></i>',
            'path' => 'path-to-x',
        ],
        [
            'name' => 'A hidden entry',
            'icon' => '',
            'path' => 'another-path',
            'visible' => false,
        ],
        [
            'name' => 'An entry with submenu',
            'icon' => '<i class="icon-diamond"></i>',
            'path' => '',
            'submenu' => SubmenuMenu::class,
        ],
    ];
}

?>
```

### Use in twig template as follows:

```twig
{% from 'macro/sidebarMenu.html.twig' import sidebarMenu %}

<ul id="Menu">
    {% for item in menus.MainMenu %}
        {{ sidebarMenu(item, loop) }}
    {% endfor %}
</ul>
```

### Content of macro/sidebarMenu.html.twig:

```twig
{% macro sidebarMenu(item, loop) %}{% spaceless %}
    {% from _self import sidebarMenu %}

    {% if isVisible %}
        <li>
            <a{% if item.target|default %} target="{{ item.target }}"{% endif %} href="{% if item.path|default %}{{ url_decode(item.path) }}{% elseif item.link|default %}{{ item.link }}{% endif %}">
                {{ item.icon|raw }}{{ item.name }}
            </a>
        </li>
    {% endif %}
{% endspaceless %}{% endmacro %}
```

### If you want to use access-control, simply add your routes to app/security.yml

Example:
```yml
access_control:
    - { path: ^/login, roles: IS_AUTHENTICATED_ANONYMOUSLY }
    - { path: ^/admin, roles: ROLE_ADMIN }
    - { path: ^/, roles: ROLE_USER }
```

