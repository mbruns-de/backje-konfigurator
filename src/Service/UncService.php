<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class UncService
{
    private ParameterBagInterface $params;

    public function __construct(ParameterBagInterface $params)
    {
        $this->params = $params;
    }

    public function getUNCPath(string $path) : string
    {

        $dirs = $this->params->get('app.unc_dirs');

        foreach ($dirs as $dir) {
            if (stripos($path, $dir['pattern']) === false) {
                continue;
            }
            $path = str_replace($dir['pattern'], $dir['unc'], $path);
            break;
        }

        //wenn nicht windows, dann backslash in slash umwandeln
        if (!$this->isWindows()) {
            $path = str_replace('\\', '/', $path);
        }

        return $path;
    }

    private function isWindows() : bool
    {
        return strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
    }
}