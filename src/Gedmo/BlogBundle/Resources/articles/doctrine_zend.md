# Doctrine 2 on Zend framework

This article describes how to connect Doctrine2 with Zend Framework.

Main implementation stuff:

- Bootstrap Doctrine allowing multi-driver support for meta data
- Configure namespaces and loaders for CLI support
- Extensive and simple implementation for any usage

[blog_reference]: http://gediminasm.org/article/doctrine-2-on-zend-framework "How to integrate doctrine2 on zend framework"

Update **2010-12-18**

- Renamed vendor Symfony Components to Component
- Removed some duplicate code and fixed naming

First of all required libraries and file structure:

- Install and configure the standard Zend application, CLI: **zf create project myapp**. If its a mist for you, read tutorial
- Download and place Doctrine - <a href="http://www.doctrine-project.org/projects/orm/download" target="new">ORM</a>, it should contain also DBAL, Common and required Symfony component packages, place them in **/myapp/library/Doctrine** directory
- Download or checkout <a href="http://github.com/doctrine/migrations" target="new">migrations</a> package, place it in /Doctrine/DBAL/
- If you would like to use other driver than Annotation, you can create Mapping folder under configs directory
- **Migration** directory should also by created in /application/configs/
- Create Entity and Proxy folders in application directory

Notice list:

- This implementation is based on **Doctrine2 RC2** version
- Last update date: **2010-12-18**

The file structure should look like this now:

```
...
/myapp
    /application
        /configs
            /Mapping
            /Migration
            application.ini
        /Entity
        /modules
        /Proxy
        Bootstrap.php
    /library
        /Doctrine
            /Common
            /DBAL
                /Migrations
                ...
            /ORM
            /Symfony
        /Zend /*if not in include path*/
    /public
...
```

Next, will use yaml mapping for our entities in this example, create a **User** entity in Yaml mapping format.

```
---
# FILE /myapp/application/configs/Mapping/Entity.User.dcm.yml
Entity\User:
  type: entity
  table: users
  id:
    id:
      type: integer
      generator:
        strategy: AUTO
  fields:
    password:
      type: string
      length: 32
    username:
      type: string
      length: 128
  oneToOne:
    role:
      targetEntity: Entity\Role
      inversedBy: users
      joinColumn:
        name: role_id
        referencedColumnName: id
  indexes:
    search_idx:
      columns: username
```

The other one is **Role** Entity. Location **/myapp/application/configs/Mapping/**

```
---
# FILE /myapp/application/configs/Mapping/Entity.Role.dcm.yml
Entity\Role:
  type: entity
  table: roles
  id:
    id:
      type: integer
      generator:
        strategy: AUTO
  fields:
    name:
      type: string
      length: 50
      unique: true
  oneToMany:
    users:
      targetEntity: Entity\User
      mappedBy: role
```

Before we start using cli to create our tables and Entities, we need to modify the **/myapp/configs/application.ini**

```
[production]
; here goes some standard configuration
phpSettings.display_startup_errors = 0
phpSettings.display_errors = 0

bootstrap.path = APPLICATION_PATH "/Bootstrap.php"
bootstrap.class = "Bootstrap"

; we will use the module for the good practise
resources.frontController.moduleDirectory = APPLICATION_PATH "/modules"
resources.frontController.moduleDefault = "default"

; layout if needed
resources.layout.layout = "layout"
resources.view[] = 
; -------------------------------------
; Here fallows Doctrine 2 Configuration
; -------------------------------------
 
; Database configuration
dbal.driver = "pdo_mysql"
dbal.host = "127.0.0.1"
dbal.user = "root"
dbal.password = "secret"
dbal.dbname = "doctrine2"
 
; location for Entity proxies and namespace
orm.proxy.path = APPLICATION_PATH "/Proxy"
orm.proxy.namespace = "Proxy"
 
; ----------------------------------------------------
; Multi driver configuration for our database metadata
; ----------------------------------------------------
 
; first is the yaml driver to read the mapping from User.yml
orm.driver.mainYamlDriver.type = "Yaml"
orm.driver.mainYamlDriver.path[] = APPLICATION_PATH "/configs/Mapping"
orm.driver.mainYamlDriver.namespace = "Entity"
 
; second is the annotation driver for example for mapping extension Entities
; notice: that the namespace should differ and the location specified to look
; for Entity metadata should exist
;orm.driver.extensionDriver.type = "Annotation"
;orm.driver.extensionDriver.path[] = APPLICATION_PATH "/../library/DoctrineExtensions/Versionable/Entity"
;orm.driver.extensionDriver.namespace = "DoctrineExtensions"
 
; later use APC for instance
orm.cache.metadata = "Array"
orm.cache.query = "Array"

[staging : production]

[cli : production]
phpSettings.display_startup_errors = 1
phpSettings.display_errors = 1
 
[testing : production]
phpSettings.display_startup_errors = 1
phpSettings.display_errors = 1
 
[development : production]
phpSettings.display_startup_errors = 1
phpSettings.display_errors = 1
resources.frontController.params.displayExceptions = 1
```

Modify the **/myapp/application/Boostrap.php** and add doctrine boot

``` php
class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    protected function _initDoctrine()
    {
        // load the doctrine class loader for Doctrine library
        require 'Doctrine/Common/ClassLoader.php';
        $classLoader = new Doctrine\Common\ClassLoader('Doctrine');
        $classLoader->register();
        // load the doctrine class loader for Symfony library components
        $classLoader = new \Doctrine\Common\ClassLoader('Symfony', 'Doctrine');
        $classLoader->register();
        // load the doctrine class loader for Entity autoloading
        $classLoader = new \Doctrine\Common\ClassLoader(
            'Entity', 
            APPLICATION_PATH
        );
        $classLoader->register();
        // read ini configuration
        $settings = $this->getOption('orm');
 
        // load proxy configuration settings
        $config = new Doctrine\ORM\Configuration;
        $config->setProxyDir($settings['proxy']['path']);
        $config->setProxyNamespace($settings['proxy']['namespace']);
        $config->setAutoGenerateProxyClasses((APPLICATION_ENV == "development"));
        // load metadata drivers
        $drivers = $settings['driver'];
        $chainDriverImpl = new Doctrine\ORM\Mapping\Driver\DriverChain();
        foreach ($drivers as $driver) {
            if ($driver['type'] == 'Annotation') {
                $driverImpl = $config->newDefaultAnnotationDriver($driver['path']);
            } else {
                $driverClassName = 'Doctrine\ORM\Mapping\Driver\\' . $driver['type'] . 'Driver';
                $driverImpl = new $driverClassName($driver['path']);
            }
            $chainDriverImpl->addDriver($driverImpl, $driver['namespace']);
        }
        $config->setMetadataDriverImpl($chainDriverImpl);
        // Set up caches
        $cacheMetadataClassName = 'Doctrine\Common\Cache\\' . $settings['cache']['metadata'] . 'Cache';
        $cache = new $cacheMetadataClassName();
        $config->setMetadataCacheImpl($cache);
        if ($settings['cache']['metadata'] != $settings['cache']['query']) {
            $cacheQueryClassName = 'Doctrine\Common\Cache\\' . $settings['cache']['query'] . 'Cache';
            $cache = new $cacheQueryClassName();
        }
        $config->setQueryCacheImpl($cache);
 
        // event manager if needed
        $evm = new Doctrine\Common\EventManager();
        // boot entity manager
        $em = Doctrine\ORM\EntityManager::create(
            $this->getOption('dbal'), 
            $config, 
            $evm
        );
        // store entity manager in registry
        Zend_Registry::set('em', $em);
        return $em;
    }
}
```

The metadata and database is the best to create through Doctrine CLI. Create the **/myapp/scripts/** directory and add the **doctrine.php** file in it, which will boostrap the CLI

``` php
<?php
/**
 * FILE: /myapp/scripts/doctrine.php
 * Doctrine 2 CLI script
 */
 
define('APPLICATION_ENV', 'cli');
define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));
 
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../library'),
    get_include_path(),
)));
 
require_once 'Zend/Application.php';
 
// Create application, bootstrap, and run
$application = new Zend_Application(
    APPLICATION_ENV,
    APPLICATION_PATH . '/configs/application.ini'
);

$application->getBootstrap()->bootstrap('doctrine');
$em = Zend_Registry::get('em');

// comment if migrations are not used
$classLoader = new Doctrine\Common\ClassLoader('Doctrine\DBAL\Migrations', 'Doctrine/DBAL');
$classLoader->register();

$helpers = array(
    'db' => new \Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper($em->getConnection()),
    'em' => new \Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper($em),
    'dialog' => new \Symfony\Component\Console\Helper\DialogHelper(),
);

$cli = new \Symfony\Component\Console\Application(
    'Doctrine Command Line Interface', 
    Doctrine\ORM\Version::VERSION
);
$cli->setCatchExceptions(true);
$helperSet = $cli->getHelperSet();
foreach ($helpers as $name => $helper) {
    $helperSet->set($helper, $name);
}

$cli->addCommands(array(
    // DBAL Commands
    new \Doctrine\DBAL\Tools\Console\Command\RunSqlCommand(),
    new \Doctrine\DBAL\Tools\Console\Command\ImportCommand(),

    // ORM Commands
    new \Doctrine\ORM\Tools\Console\Command\ClearCache\MetadataCommand(),
    new \Doctrine\ORM\Tools\Console\Command\ClearCache\ResultCommand(),
    new \Doctrine\ORM\Tools\Console\Command\ClearCache\QueryCommand(),
    new \Doctrine\ORM\Tools\Console\Command\SchemaTool\CreateCommand(),
    new \Doctrine\ORM\Tools\Console\Command\SchemaTool\UpdateCommand(),
    new \Doctrine\ORM\Tools\Console\Command\SchemaTool\DropCommand(),
    new \Doctrine\ORM\Tools\Console\Command\EnsureProductionSettingsCommand(),
    new \Doctrine\ORM\Tools\Console\Command\ConvertDoctrine1SchemaCommand(),
    new \Doctrine\ORM\Tools\Console\Command\GenerateRepositoriesCommand(),
    new \Doctrine\ORM\Tools\Console\Command\GenerateEntitiesCommand(),
    new \Doctrine\ORM\Tools\Console\Command\GenerateProxiesCommand(),
    new \Doctrine\ORM\Tools\Console\Command\ConvertMappingCommand(),
    new \Doctrine\ORM\Tools\Console\Command\RunDqlCommand(),
    new \Doctrine\ORM\Tools\Console\Command\ValidateSchemaCommand(),
    
    // Migrations Commands, remove if not needed
    new \Doctrine\DBAL\Migrations\Tools\Console\Command\DiffCommand(),
    new \Doctrine\DBAL\Migrations\Tools\Console\Command\ExecuteCommand(),
    new \Doctrine\DBAL\Migrations\Tools\Console\Command\GenerateCommand(),
    new \Doctrine\DBAL\Migrations\Tools\Console\Command\MigrateCommand(),
    new \Doctrine\DBAL\Migrations\Tools\Console\Command\StatusCommand(),
    new \Doctrine\DBAL\Migrations\Tools\Console\Command\VersionCommand(),
));
$cli->run();
```

If you use migrations create ../myapp/scripts/migrations.yml for migration configuration:

```
---
name: Doctrine Migrations
migrations_namespace: DoctrineMigration
table_name: ext_migration_versions
migrations_directory: ../application/configs/Migration
```

Create the shell or batch script(windows) to run it through PHP cli

**/myapp/scripts/doctrine.bat** dont forget to modify the **php.exe** path and the **script** location if it difers

```
@echo off
echo Running Doctrine CLI.
"C:\php\php.exe" -f doctrine.php %1 %2 %3 %4 %5 %6 %7 %8 %9
```

For linux users

**/myapp/scripts/doctrine.sh** dont forget to modify the **php bin** path if it difers, and run **chmod +x**

```
#!/usr/bin/env php
chdir(dirname(__FILE__));
include('doctrine.php');
```

Now lets go to the Shell or Command Prompt, go to **/myapp/scripts/** and run these commands:

**Notice:** that the last argument(1, 2 commands) is the location of your application

**Notice:** before running the third command, database must be created, empty and entities must be created also[first command]

- doctrine orm:generate-entities ../application
- doctrine orm:generate-proxies ../application/Proxy
- if you do not use migrations, run create schema: *doctrine orm:schema-tool:create*
- doctrine migrations:diff
- doctrine migrations:migrate

After running first command the Entities should be created in the **/application/Entity** directory, unless you specified another. After running the second would create proxies for the lazy loading. Then if you used migrations, 3 command would create migration from the entities found and 4 command would migrate to these changes. 

By this point, we should have everything in place. Lets play around a bit. First, if you have not created index controller, create it and modify a bit to test our new stuff, file: **/myapp/application/modules/default/controllers/IndexController.php**

``` php
class IndexController extends Zend_Controller_Action
{
    protected $_em;

    public function init()
    {
        $this->_em = Zend_Registry::get('em');
    }
    
    public function indexAction()
    {
        $user = $this->_em->getRepository('Entity\User')->find(1);
        if (!$user) {
            // create groups and user only once
            $this->populate();
            $user = $this->_em->getRepository('Entity\User')->find(1);
        }
        $this->view->user = $user;
    }
    
    public function populate()
    {
        // create Roles
        $guestRole = new Entity\Role;
        $guestRole->setName('guest');
        $this->_em->persist($guestRole);
        
        $adminRole = new Entity\Role;
        $adminRole->setName('admin');
        $this->_em->persist($adminRole);
        
        $memberRole = new Entity\Role;
        $memberRole->setName('member');
        $this->_em->persist($memberRole);
        
        // create users
        $user = new Entity\User;
        $user->setUsername('Master');
        $user->setPassword('secret');
        $user->setRole($adminRole);
        $this->_em->persist($user);
        
        $this->_em->flush();
    }
}
```

Now if you run index action few groups will be created and an user, which is passed to the view: **/application/modules/default/view/index/index.phtml**

``` php
hello <php? echo $this->user->getUsername(); ?>
```

Also probably layout is required, create:

``` php
<!-- FILE: ../myapp/modules/default/views/scripts/layout.phtml -->
<!DOCTYPE html>
<html>
<head>
</head>
<body>
    <div id="content">
        <?php echo $this->layout()->content; ?>
    </div>
</body>
</html>
```

If something went wrong or you cannot understand some parts of code, you can [download][1] the application which was made in this example. **Notice:** that it will require Zend library in the /myapp/library folder if you do not have it in the include path. Also you will need to adjust the database credentials.

Thats it. Now you can easily add another features and enjoy the Doctrine 2 on Zend framework

 [1]: /files/myapp.rar