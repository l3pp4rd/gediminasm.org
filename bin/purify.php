<?php

define('VENDOR_PATH', realpath(__DIR__ . '/../vendor'));

$classLoaderFile = VENDOR_PATH . '/doctrine-common/lib/Doctrine/Common/ClassLoader.php';
if (!file_exists($classLoaderFile)) {
    die('cannot find vendor, run: php bin/vendors.php');
}

require_once $classLoaderFile;
$classLoader = new Doctrine\Common\ClassLoader('Symfony', VENDOR_PATH.'/symfony/src');
$classLoader->register();

$finder = new Symfony\Component\Finder\Finder;
$finder->files()
    ->name('*.php')
    ->name('*.js')
    ->name('*.twig')
    ->in(__DIR__ . '/../src')
;

foreach ($finder as $fileInfo) {
    if (!$fileInfo->isReadable()) {
        continue;
    }
    $count = 0;
    $total = 0;
    $needsSave = false;
    $content = file_get_contents($fileInfo->getRealPath());

    $content = str_replace("\t", '    ', $content, $count);
    $total += $count;

    $content = str_replace("\r\n", "\n", $content, $count);
    $total += $count;

    $needsSave = $total != 0;

    if ($needsSave) {
        file_put_contents($fileInfo->getRealPath(), $content);
        echo $fileInfo->getRealPath() . PHP_EOL;
    }
}

echo 'done';