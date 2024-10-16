<?php

namespace App\Twig;

use Twig\Environment;
use Twig\TwigFilter;
use Twig\Extension\AbstractExtension;
// use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\HttpFoundation\Request;

class AppExtension extends AbstractExtension
{
    private $twig;
    private $kernel;
    private $featureService;
    private $uncService;
    private $encService;
    private $imgService;

    public function __construct(Environment $twig, KernelInterface $kernel)
    {
        $this->twig = $twig;
        $this->kernel = $kernel;
    }

    public function getFilters()
    {
        return [
            new TwigFilter('app_format_time', [$this, 'formatTime']),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new \Twig\TwigFunction('app_get_pagination', [$this, 'getPagination']),
        ];
    }

    public function getPagination($count, $limit, $currentPage, $routeName, $extraClass = null, $divName = null)
    {
        if($divName == "" || $divName == null)
        {
            $divName = "app";
        }

        return $this->twig->render('_pagination.html.twig', [
            'count' => $count,
            'limit' => $limit,
            'currentPage' => $currentPage,
            'routeName' => $routeName,
            'extraClass' => $extraClass,
            'divName' => $divName,
        ]);
    }
}

