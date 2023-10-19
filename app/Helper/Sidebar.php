<?php

declare(strict_types=1);

namespace Platine\App\Helper;

use Platine\Framework\Auth\AuthorizationInterface;
use Platine\Lang\Lang;
use Platine\Route\RouteCollectionInterface;
use Platine\Route\Router;
use Platine\Stdlib\Helper\Str;

/**
 * @class Sidebar
 * @package Platine\App\Helper
 */
class Sidebar
{
    /**
     * The sidebar data
     * @var array<string, array<int, array<string, mixed>>>
     */
    protected array $data = [];

    /**
     * The Router instance
     * @var Router
     */
    protected Router $router;

    /**
     * The authorization instance
     * @var AuthorizationInterface
     */
    protected AuthorizationInterface $authorization;

    /**
     * The Lang instance
     * @var Lang
     */
    protected Lang $lang;

    /**
     * Create new instance
     * @param Router $router
     * @param Lang $lang
     * @param AuthorizationInterface $authorization
     */
    public function __construct(
        Router $router,
        Lang $lang,
        AuthorizationInterface $authorization
    ) {
        $this->lang = $lang;
        $this->router = $router;
        $this->authorization = $authorization;
    }

    /**
     * Add new sidebar
     * @param string $group
     * @param string $title
     * @param string $name
     * @param array<string, mixed> $params
     * @param array<string, mixed> $extras
     * @return self
     */
    public function add(string $group, string $title, string $name, array $params = [], array $extras = []): self
    {
        if (empty($group)) {
            $group = 'Actions';
        }
        if (!isset($this->data[$group])) {
            $this->data[$group] = [];
        }
        $this->data[$group][] = [
            'title' => $title,
            'name' => $name,
            'params' => $params,
            'extras' => $extras,
        ];

        return $this;
    }
    /**
     * Render the sidebar
     * @return string
     */
    public function render(): string
    {
        $str = '';

        /** @var RouteCollectionInterface $routes */
        $routes = $this->router->routes();

        foreach ($this->data as $group => $sidebar) {
            $str .= sprintf('<div class="list-group page-sidebar"><a href="#" class="sidebar-action list-group-item list-group-item-dark"><b>%s</b></a>', $group);

            foreach ($sidebar as $data) {
                $name = $data['name'];
                $permission = $routes->get($name)->getAttribute('permission');
                if ($permission !== null && !$this->authorization->isGranted($permission)) {
                    continue;
                }

                $attributes = '';
                $query = '';
                $title = Str::ucfirst($data['title']);

                if (!empty($data['extras'])) {
                    $extras = $data['extras'];
                    if (isset($extras['confirm'])) {
                        $confirmMessage = $extras['confirm_message'] ?? $this->lang->tr('Voulez-vous procéder à cette opération [%s] ?', $title);
                        unset($extras['confirm'], $extras['confirm_message']);
                        $extras['data-text-confirm'] = $confirmMessage;
                    }
                    if (isset($extras['query'])) {
                        $query = http_build_query($extras['query']);
                        unset($extras['query']);
                    }
                    $attributes = Str::toAttribute($extras);
                }
                $url = $this->router->getUri($data['name'], $data['params']) . (!empty($query) ? '?' . $query : '');
                $str .= sprintf('<a %s class="list-group-item list-group-item-action" href="%s">%s</a>', $attributes, $url, $title);
            }
            $str .= '</div>';
        }

        return $str;
    }
}
