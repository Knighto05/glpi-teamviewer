<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInita8b2d5d40f5d5ee0ff539cb7ea7fc7aa
{
    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->classMap = ComposerStaticInita8b2d5d40f5d5ee0ff539cb7ea7fc7aa::$classMap;

        }, null, ClassLoader::class);
    }
}
