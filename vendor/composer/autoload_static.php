<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitf01550748f4b2b87e8f82a16522703eb
{
    public static $prefixLengthsPsr4 = array (
        'G' => 
        array (
            'Grpc\\' => 5,
            'Google\\Protobuf\\' => 16,
            'GPBMetadata\\Google\\Protobuf\\' => 28,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Grpc\\' => 
        array (
            0 => __DIR__ . '/..' . '/grpc/grpc/src/php/lib/Grpc',
        ),
        'Google\\Protobuf\\' => 
        array (
            0 => __DIR__ . '/..' . '/google/protobuf/php/src/Google/Protobuf',
        ),
        'GPBMetadata\\Google\\Protobuf\\' => 
        array (
            0 => __DIR__ . '/..' . '/google/protobuf/php/src/GPBMetadata/Google/Protobuf',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitf01550748f4b2b87e8f82a16522703eb::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitf01550748f4b2b87e8f82a16522703eb::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
