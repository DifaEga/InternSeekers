<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit80a0334feb3e54c9bcb2ff6b85b6a1ec
{
    public static $prefixLengthsPsr4 = array (
        'P' => 
        array (
            'PHPMailer\\PHPMailer\\' => 20,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'PHPMailer\\PHPMailer\\' => 
        array (
            0 => __DIR__ . '/..' . '/phpmailer/phpmailer/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit80a0334feb3e54c9bcb2ff6b85b6a1ec::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit80a0334feb3e54c9bcb2ff6b85b6a1ec::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit80a0334feb3e54c9bcb2ff6b85b6a1ec::$classMap;

        }, null, ClassLoader::class);
    }
}