<?php
/**
 * Created by PhpStorm.
 * User: N1
 * Date: 02.06.17
 * Time: 15:26
 */

namespace MainBundle\Controller;

use MainBundle\MainBundle;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\HttpKernel\Log\DebugLoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use \Symfony\Bundle\TwigBundle\Controller\ExceptionController as TwigException;

class ExceptionController extends BaseController
{
    function showException(Request $request, FlattenException $exception, DebugLoggerInterface $logger = null){
        $uri = $request->getRequestUri();
        if(strpos($uri,'//') !== false){
            $uri = str_replace('//','/',$uri);
            return new RedirectResponse($uri, 302);
        }

        if(strpos($uri,'/app_dev.php/app_dev.php/') !== false){
            $uri = str_replace('/app_dev.php/app_dev.php/','/app_dev.php/',$uri);
            return new RedirectResponse($uri, 302);
        }

        if(preg_match('/\.(jpeg|gif|jpg|png)$/',$uri)){ // image request
            //$file = __DIR__.'/../../../web/placeholder.svg';
            //return new BinaryFileResponse($file);
        }

        $code = $exception->getStatusCode();
        switch ($code){
            case 500:
                $message = 'При загрузке этой станицы возникли неполадки.';
                break;
            case 404:
                $message = 'Страница не найдена';
                break;
            case 403:
                $message = 'При загрузке этой станицы возникли неполадки.';
                break;
            default:
                $message = $exception->getMessage();
        }

        $pageParams['code'] = $code;
        $pageParams['message'] = $message;
        $pageParams['error'] = $exception;
        return new Response($this->twig->render(
            'MainBundle:Exception:base.html.twig',$pageParams
        ));
    }


    protected $twig;

    /**
     * @var bool Show error (false) or exception (true) pages by default
     */
    protected $debug;

    public function __construct(\Twig_Environment $twig, $debug)
    {
        $this->twig = $twig;
        $this->debug = $debug;
    }

    /**
     * Converts an Exception to a Response.
     *
     * A "showException" request parameter can be used to force display of an error page (when set to false) or
     * the exception page (when true). If it is not present, the "debug" value passed into the constructor will
     * be used.
     *
     * @param Request              $request   The request
     * @param FlattenException     $exception A FlattenException instance
     * @param DebugLoggerInterface $logger    A DebugLoggerInterface instance
     *
     * @return Response
     *
     * @throws \InvalidArgumentException When the exception template does not exist
     */
    public function showAction(Request $request, FlattenException $exception, DebugLoggerInterface $logger = null)
    {
        $currentContent = $this->getAndCleanOutputBuffering($request->headers->get('X-Php-Ob-Level', -1));
        $showException = $request->attributes->get('showException', $this->debug); // As opposed to an additional parameter, this maintains BC

        $code = $exception->getStatusCode();

        return new Response($this->twig->render(
            (string) $this->findTemplate($request, $request->getRequestFormat(), $code, $showException),
            array(
                'status_code' => $code,
                'status_text' => isset(Response::$statusTexts[$code]) ? Response::$statusTexts[$code] : '',
                'exception' => $exception,
                'logger' => $logger,
                'currentContent' => $currentContent,
            )
        ));
    }

    /**
     * @param int $startObLevel
     *
     * @return string
     */
    protected function getAndCleanOutputBuffering($startObLevel)
    {
        if (ob_get_level() <= $startObLevel) {
            return '';
        }

        Response::closeOutputBuffers($startObLevel + 1, true);

        return ob_get_clean();
    }

    /**
     * @param Request $request
     * @param string  $format
     * @param int     $code          An HTTP response status code
     * @param bool    $showException
     *
     * @return string
     */
    protected function findTemplate(Request $request, $format, $code, $showException)
    {
        $name = $showException ? 'exception' : 'error';
        if ($showException && 'html' == $format) {
            $name = 'exception_full';
        }

        // For error pages, try to find a template for the specific HTTP status code and format
        if (!$showException) {
            $template = sprintf('@Twig/Exception/%s%s.%s.twig', $name, $code, $format);
            if ($this->templateExists($template)) {
                return $template;
            }
        }

        // try to find a template for the given format
        $template = sprintf('@Twig/Exception/%s.%s.twig', $name, $format);
        if ($this->templateExists($template)) {
            return $template;
        }

        // default to a generic HTML exception
        $request->setRequestFormat('html');

        return sprintf('@Twig/Exception/%s.html.twig', $showException ? 'exception_full' : $name);
    }

    // to be removed when the minimum required version of Twig is >= 3.0
    protected function templateExists($template)
    {
        $template = (string) $template;

        $loader = $this->twig->getLoader();
        if ($loader instanceof \Twig_ExistsLoaderInterface || method_exists($loader, 'exists')) {
            return $loader->exists($template);
        }

        try {
            $loader->getSourceContext($template)->getCode();

            return true;
        } catch (\Twig_Error_Loader $e) {
        }

        return false;
    }
}