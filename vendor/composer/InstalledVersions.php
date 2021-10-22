<?php











namespace Composer;

use Composer\Autoload\ClassLoader;
use Composer\Semver\VersionParser;






class InstalledVersions
{
private static $installed = array (
  'root' => 
  array (
    'pretty_version' => '1.0.0+no-version-set',
    'version' => '1.0.0.0',
    'aliases' => 
    array (
    ),
    'reference' => NULL,
    'name' => '__root__',
  ),
  'versions' => 
  array (
    '__root__' => 
    array (
      'pretty_version' => '1.0.0+no-version-set',
      'version' => '1.0.0.0',
      'aliases' => 
      array (
      ),
      'reference' => NULL,
    ),
    'composer/ca-bundle' => 
    array (
      'pretty_version' => '1.2.10',
      'version' => '1.2.10.0',
      'aliases' => 
      array (
      ),
      'reference' => '9fdb22c2e97a614657716178093cd1da90a64aa8',
    ),
    'jakoch/phantomjs-installer' => 
    array (
      'pretty_version' => '2.1.1-p08',
      'version' => '2.1.1.0-patch08',
      'aliases' => 
      array (
      ),
      'reference' => '7775820cf5eae8962cf68ab44757238e4d3136cc',
    ),
    'jonnyw/php-phantomjs' => 
    array (
      'pretty_version' => 'v4.6.1',
      'version' => '4.6.1.0',
      'aliases' => 
      array (
      ),
      'reference' => 'f35764503f6828908fd1108c960080edf915eabe',
    ),
    'kegi/netscape-cookie-file-handler' => 
    array (
      'pretty_version' => '1.0.3',
      'version' => '1.0.3.0',
      'aliases' => 
      array (
      ),
      'reference' => '44fb096f9fb83d444d7f16b07d449220f7219761',
    ),
    'kub-at/php-simple-html-dom-parser' => 
    array (
      'pretty_version' => '1.9.1',
      'version' => '1.9.1.0',
      'aliases' => 
      array (
      ),
      'reference' => 'ff22f98bfd9235115c128059076f3eb740d66913',
    ),
    'mobiledetect/mobiledetectlib' => 
    array (
      'pretty_version' => '2.8.37',
      'version' => '2.8.37.0',
      'aliases' => 
      array (
      ),
      'reference' => '9841e3c46f5bd0739b53aed8ac677fa712943df7',
    ),
    'ovidigital/js-object-to-json' => 
    array (
      'pretty_version' => '1.0.3',
      'version' => '1.0.3.0',
      'aliases' => 
      array (
      ),
      'reference' => 'cc6f5f996fdd2d05701204c108fef32406f91c97',
    ),
    'phpfastcache/phpfastcache' => 
    array (
      'pretty_version' => '8.0.6',
      'version' => '8.0.6.0',
      'aliases' => 
      array (
      ),
      'reference' => 'c93bd7d341176b64b428953365ef67002af9f61e',
    ),
    'phpmailer/phpmailer' => 
    array (
      'pretty_version' => 'v6.5.0',
      'version' => '6.5.0.0',
      'aliases' => 
      array (
      ),
      'reference' => 'a5b5c43e50b7fba655f793ad27303cd74c57363c',
    ),
    'phpseclib/mcrypt_compat' => 
    array (
      'pretty_version' => '1.0.11',
      'version' => '1.0.11.0',
      'aliases' => 
      array (
      ),
      'reference' => '4570bd80edb410179af3b243a568ff366b528558',
    ),
    'phpseclib/phpseclib' => 
    array (
      'pretty_version' => '2.0.32',
      'version' => '2.0.32.0',
      'aliases' => 
      array (
      ),
      'reference' => 'f5c4c19880d45d0be3e7d24ae8ac434844a898cd',
    ),
    'psr/cache' => 
    array (
      'pretty_version' => '1.0.1',
      'version' => '1.0.1.0',
      'aliases' => 
      array (
      ),
      'reference' => 'd11b50ad223250cf17b86e38383413f5a6764bf8',
    ),
    'psr/container' => 
    array (
      'pretty_version' => '1.1.1',
      'version' => '1.1.1.0',
      'aliases' => 
      array (
      ),
      'reference' => '8622567409010282b7aeebe4bb841fe98b58dcaf',
    ),
    'psr/container-implementation' => 
    array (
      'provided' => 
      array (
        0 => '1.0',
      ),
    ),
    'psr/simple-cache' => 
    array (
      'pretty_version' => '1.0.1',
      'version' => '1.0.1.0',
      'aliases' => 
      array (
      ),
      'reference' => '408d5eafb83c57f6365a3ca330ff23aa4a5fa39b',
    ),
    'ptcong/php-javascript-unpacker' => 
    array (
      'pretty_version' => '1.0.0',
      'version' => '1.0.0.0',
      'aliases' => 
      array (
      ),
      'reference' => '22302c9d5b0805a908494c56e169a3e6bb7b2e1e',
    ),
    'symfony/config' => 
    array (
      'pretty_version' => 'v3.4.47',
      'version' => '3.4.47.0',
      'aliases' => 
      array (
      ),
      'reference' => 'bc6b3fd3930d4b53a60b42fe2ed6fc466b75f03f',
    ),
    'symfony/dependency-injection' => 
    array (
      'pretty_version' => 'v3.4.47',
      'version' => '3.4.47.0',
      'aliases' => 
      array (
      ),
      'reference' => '51d2a2708c6ceadad84393f8581df1dcf9e5e84b',
    ),
    'symfony/filesystem' => 
    array (
      'pretty_version' => 'v3.4.47',
      'version' => '3.4.47.0',
      'aliases' => 
      array (
      ),
      'reference' => 'e58d7841cddfed6e846829040dca2cca0ebbbbb3',
    ),
    'symfony/polyfill-ctype' => 
    array (
      'pretty_version' => 'v1.23.0',
      'version' => '1.23.0.0',
      'aliases' => 
      array (
      ),
      'reference' => '46cd95797e9df938fdd2b03693b5fca5e64b01ce',
    ),
    'symfony/yaml' => 
    array (
      'pretty_version' => 'v3.4.47',
      'version' => '3.4.47.0',
      'aliases' => 
      array (
      ),
      'reference' => '88289caa3c166321883f67fe5130188ebbb47094',
    ),
    'tholu/php-packer' => 
    array (
      'pretty_version' => '0.2.0',
      'version' => '0.2.0.0',
      'aliases' => 
      array (
      ),
      'reference' => 'b6f63a6e1112a27db048353fbab50539a4a75764',
    ),
    'twig/twig' => 
    array (
      'pretty_version' => 'v1.44.4',
      'version' => '1.44.4.0',
      'aliases' => 
      array (
      ),
      'reference' => '4d400421528e9fa40caaffcf7824c172526dd99d',
    ),
    'ua-parser/uap-php' => 
    array (
      'pretty_version' => 'v3.9.14',
      'version' => '3.9.14.0',
      'aliases' => 
      array (
      ),
      'reference' => 'b796c5ea5df588e65aeb4e2c6cce3811dec4fed6',
    ),
  ),
);
private static $canGetVendors;
private static $installedByVendor = array();







public static function getInstalledPackages()
{
$packages = array();
foreach (self::getInstalled() as $installed) {
$packages[] = array_keys($installed['versions']);
}


if (1 === \count($packages)) {
return $packages[0];
}

return array_keys(array_flip(\call_user_func_array('array_merge', $packages)));
}









public static function isInstalled($packageName)
{
foreach (self::getInstalled() as $installed) {
if (isset($installed['versions'][$packageName])) {
return true;
}
}

return false;
}














public static function satisfies(VersionParser $parser, $packageName, $constraint)
{
$constraint = $parser->parseConstraints($constraint);
$provided = $parser->parseConstraints(self::getVersionRanges($packageName));

return $provided->matches($constraint);
}










public static function getVersionRanges($packageName)
{
foreach (self::getInstalled() as $installed) {
if (!isset($installed['versions'][$packageName])) {
continue;
}

$ranges = array();
if (isset($installed['versions'][$packageName]['pretty_version'])) {
$ranges[] = $installed['versions'][$packageName]['pretty_version'];
}
if (array_key_exists('aliases', $installed['versions'][$packageName])) {
$ranges = array_merge($ranges, $installed['versions'][$packageName]['aliases']);
}
if (array_key_exists('replaced', $installed['versions'][$packageName])) {
$ranges = array_merge($ranges, $installed['versions'][$packageName]['replaced']);
}
if (array_key_exists('provided', $installed['versions'][$packageName])) {
$ranges = array_merge($ranges, $installed['versions'][$packageName]['provided']);
}

return implode(' || ', $ranges);
}

throw new \OutOfBoundsException('Package "' . $packageName . '" is not installed');
}





public static function getVersion($packageName)
{
foreach (self::getInstalled() as $installed) {
if (!isset($installed['versions'][$packageName])) {
continue;
}

if (!isset($installed['versions'][$packageName]['version'])) {
return null;
}

return $installed['versions'][$packageName]['version'];
}

throw new \OutOfBoundsException('Package "' . $packageName . '" is not installed');
}





public static function getPrettyVersion($packageName)
{
foreach (self::getInstalled() as $installed) {
if (!isset($installed['versions'][$packageName])) {
continue;
}

if (!isset($installed['versions'][$packageName]['pretty_version'])) {
return null;
}

return $installed['versions'][$packageName]['pretty_version'];
}

throw new \OutOfBoundsException('Package "' . $packageName . '" is not installed');
}





public static function getReference($packageName)
{
foreach (self::getInstalled() as $installed) {
if (!isset($installed['versions'][$packageName])) {
continue;
}

if (!isset($installed['versions'][$packageName]['reference'])) {
return null;
}

return $installed['versions'][$packageName]['reference'];
}

throw new \OutOfBoundsException('Package "' . $packageName . '" is not installed');
}





public static function getRootPackage()
{
$installed = self::getInstalled();

return $installed[0]['root'];
}







public static function getRawData()
{
return self::$installed;
}



















public static function reload($data)
{
self::$installed = $data;
self::$installedByVendor = array();
}




private static function getInstalled()
{
if (null === self::$canGetVendors) {
self::$canGetVendors = method_exists('Composer\Autoload\ClassLoader', 'getRegisteredLoaders');
}

$installed = array();

if (self::$canGetVendors) {
foreach (ClassLoader::getRegisteredLoaders() as $vendorDir => $loader) {
if (isset(self::$installedByVendor[$vendorDir])) {
$installed[] = self::$installedByVendor[$vendorDir];
} elseif (is_file($vendorDir.'/composer/installed.php')) {
$installed[] = self::$installedByVendor[$vendorDir] = require $vendorDir.'/composer/installed.php';
}
}
}

$installed[] = self::$installed;

return $installed;
}
}
