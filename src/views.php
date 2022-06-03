<?php
/*
 * « Copyright © 2021, Steodec
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the “Software”), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
 *
 * The Software is provided “as is”, without warranty of any kind, express or implied, including but not limited to the warranties of merchantability, fitness for a particular purpose and noninfringement. In no event shall the authors or copyright holders X be liable for any claim, damages or other liability, whether in an action of contract, tort or otherwise, arising from, out of or in connection with the software or the use or other dealings in the Software.
 *
 * Except as contained in this notice, the name of the <copyright holders> shall not be used in advertising or otherwise to promote the sale, use or other dealings in this Software without prior written authorization from the Steodec. »
 */

namespace Steodec\Controllers;


use Bluetel\Twig\TruncateExtension;
use Exception;
use JetBrains\PhpStorm\NoReturn;
use ReflectionException;
use SalernoLabs\PHPToXML\Convert;
use Steodec\Router\Attributes\Route;
use Steodec\Router\Router\RouterConfig;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Extension\DebugExtension;
use Twig\Extra\String\StringExtension;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFilter;
use Twig\TwigFunction;

trait views {
    /**
     * @param array|object $object
     * @param int $httpStatusCode
     *
     * @return void
     * @throws Exception
     */
    public function renderXML(array|object $object, int $httpStatusCode = HttpStatus::OK): void {
        $converter = new Convert();
        header('Content-type: application/xml');
        http_response_code($httpStatusCode);
        $xml = $converter->setObjectData($object);
        echo $xml->convert();
    }

    /**
     * @param array|object $object
     * @param int $httpStatusCode
     *
     * @return void
     */
    public function renderJSON(array|object $object, int $httpStatusCode = HttpStatus::OK): void {
        header('Content-type: application/json');
        http_response_code($httpStatusCode);
        echo json_encode($object);
    }

    /**
     * @param string $templatePath
     * @param array|null $params
     * @param array|null $function
     * @param bool $withoutBase
     *
     * @return void
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function renderHTML(string $templatePath, ?array $params = [], ?array $function = NULL, bool $withoutBase = FALSE) {
        $params['titre']   = $params['titre'] ?? "";
        $params['session'] = $_SESSION;
        $loader            = new FilesystemLoader(Constants::TEMPLATE_PATH);
        $twig              = new Environment($loader, ['debug' => TRUE]);
        $twig->addExtension(new DebugExtension());
        $twig->addExtension(new StringExtension());
        $twig->addFunction(new TwigFunction('getRoute', fn (?string $path = NULL, ?array $params = NULL) => $this->returnRoute($path, $params)));
        $twig->addGlobal('_host', $_SERVER['SERVER_NAME']);
        $twig->addGlobal('env', $_ENV);
        $twig->addGlobal('_get', $_GET);
        $twig->addGlobal('_post', $_POST);
        $twig->addFilter(new TwigFilter('json_decode', fn(string $a) => json_decode($a)));
        echo $twig->render($templatePath, $params);
    }

    /**
     * @param string $newPath
     *
     * @return void
     * @throws ReflectionException
     */
    #[NoReturn] protected function redirect(string $newPath): void {
        $route = $this->returnRoute($newPath);
        header('Location: ' . $route);
        exit;
    }

    /**
     * @param string|null $routeName
     * @param array|null $params
     *
     * @return array|string
     * @throws ReflectionException
     */
    public function returnRoute(?string $routeName = NULL, ?array $params = []): array|string {
        $routes = (new RouterConfig('App\\controllers'))->getRoute();
        if (is_null($routeName)):
            return $routes;
        else:
            $route = array_filter($routes, fn(Route $el) => $el->getName() == $routeName);
            $path  = array_values($route)[0]->getPath();
            if (str_contains($path, ":")):
                $re = '/:.+/m';
                preg_match_all($re, $path, $matches);
                foreach ($matches[0] as $match):
                    $path = str_replace($match, $params[substr($match, 1)], $path);
                endforeach;
            endif;
            return $path;
        endif;
    }
}