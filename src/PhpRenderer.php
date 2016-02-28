<?php
/**
 * Slim Framework (http://slimframework.com)
 *
 * @link      https://github.com/slimphp/PHP-View
 * @copyright Copyright (c) 2011-2015 Josh Lockhart
 * @license   https://github.com/slimphp/PHP-View/blob/master/LICENSE.md (MIT License)
 */
namespace Slim\Views;

use Psr\Http\Message\ResponseInterface;

/**
 * Php View
 *
 * Render PHP view scripts into a PSR-7 Response object
 */
class PhpRenderer
{
    /**
     * @var string
     */
    protected $templatePath;

    /**
     * SlimRenderer constructor.
     *
     * @param string $templatePath
     */
    public function __construct($templatePath = "")
    {
        $this->templatePath = $templatePath;
    }

    /**
     * Render a template
     *
     * $data cannot contain template as a key
     *
     * throws RuntimeException if $templatePath . $template does not exist
     *
     * @param \ResponseInterface $response
     * @param                    $template
     * @param array              $data
     *
     * @return ResponseInterface
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function generate_render_func(){
        $path = $this->templatePath;
        $func = function($template, array $data = []) use ($path) {
            if (!is_file($path . $template)) {
                throw new \RuntimeException("View cannot render `$template` because the template does not exist");
            }
            extract($data);
            include $path . $template;
        };
        return $func;
    }
    public function render(ResponseInterface $response, $template, array $data = [])
    {
        if (isset($data['template'])) {
            throw new \InvalidArgumentException("Duplicate template key found");
        }

        if (!is_file($this->templatePath . $template)) {
            throw new \RuntimeException("View cannot render `$template` because the template does not exist");
        }
        $data["render"] = $this->generate_render_func();

        $render = function ($template, $data) {
            extract($data);
            include $template;
        };

        ob_start();
        $render($this->templatePath . $template, $data);
        $output = ob_get_clean();

        $response->getBody()->write($output);

        return $response;
    }
}
