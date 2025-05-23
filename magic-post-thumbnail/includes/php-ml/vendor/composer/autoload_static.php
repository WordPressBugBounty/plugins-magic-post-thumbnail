<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit34333ccae310ca4c4dbf3cd06423c849
{
    public static $prefixLengthsPsr4 = array (
        'P' => 
        array (
            'Phpml\\' => 6,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Phpml\\' => 
        array (
            0 => __DIR__ . '/..' . '/php-ai/php-ml/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit34333ccae310ca4c4dbf3cd06423c849::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit34333ccae310ca4c4dbf3cd06423c849::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit34333ccae310ca4c4dbf3cd06423c849::$classMap;

        }, null, ClassLoader::class);
    }
}
