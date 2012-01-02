# Smarty 3 extension for Zend Framework

**Smarty3** extension is easy to integrate and with full compatibility of view and
layout templates which supports all standard features like modules and view rendering
in ajax, json, xml. All helpers with Smarty 3 are working nicely without any intervention.

Features:

- Layout and view rendering by standard rules
- Static template path for each module
- All helper support including ajax, json, xml contexts

[blog_reference]: http://gediminasm.org/article/smarty-3-extension-for-zend-framework "Smarty 3 extension for Zend framework, with full: layout and view template support"

Update **2010-10-30**

- Added support for partials using smarty view, clone of view was updated
- Removed dependency on config storage on bootstrap

**Notice list:**

- You can download full aplication [sources here][1]. Notice: that Zend library must be in your include path or /library directory.
- Last update date: **2010-10-30**
- This extension will only work with **Smarty 3** version

This article will cover the basic installation and functionality of **Smarty 3** extension

Content:

- [Preparing][2] the smarty library
- Setting up the application [configuration][3]
- Smarty 3 view [bootstrap][4]
- Seeing it in [action][5]

## Downloading Smarty 3 {#including-smarty}

First of all we will need to download Smarty 3:

- Go to the smarty [downloads][6]
- Select and download the latest smarty 3 release
- Extract it and copy contents of directory **libs** into **/myapp/library/Smarty/**

After doing that your application structure should look something like this:

```
...
/myapp
    /application
        /configs
            application.ini
        /modules
        Bootstrap.php
    /library
        /Smarty
            /plugins
            /sysplugins
            /debug.tpl
            /Smarty.class.php
    /public
...
```

## Setting up the application.ini configuration {#ini}

For good practise we will use modules. Your basic configuration can look like:

```
[production]
phpSettings.display_startup_errors = 0
phpSettings.display_errors = 0

bootstrap.path = APPLICATION_PATH "/Bootstrap.php"
bootstrap.class = "Bootstrap"

resources.frontController.moduleDirectory = APPLICATION_PATH "/modules"
resources.frontController.moduleDefault = "default"

resources.layout.layout = "layout"
resources.view[] =

; --- Autoloading Prefixes ---

autoloaderNamespaces.extension[] = "Ext_"

; --- Smarty ---

smarty.caching = 1
smarty.cache_lifetime = 14400 ; 4 hours
smarty.template_dir = APPLICATION_PATH "/templates/"
smarty.compile_dir = APPLICATION_PATH "/tmp/smarty_compile/"
smarty.config_dir = ""
smarty.cache_dir = APPLICATION_PATH "/tmp/smarty_cache/"
smarty.left_delimiter = "{"
smarty.right_delimiter = "}"

[staging : production]

[testing : production]
phpSettings.display_startup_errors = 1
phpSettings.display_errors = 1

[development : production]
phpSettings.display_startup_errors = 1
phpSettings.display_errors = 1
resources.frontController.params.displayExceptions = 1

smarty.caching = 0
```

This configuration will enable the layout and set the default smarty settings. Also you will require to add /tmp directory in your application catalog. The prefix autoloader was configured also, which should be available in /library directory.

After some modifications you should have structure like:

```
...
/myapp
    /application
        /configs
            application.ini
        /modules
            /default
                /controllers
                    ...
                /views
                    ...
        /tmp
        Bootstrap.php
    /library
        /Smarty
            /plugins
            /sysplugins
            /debug.tpl
            /Smarty.class.php
        /Ext
    /public
...
```

## Now the most important: Smarty View class {#view}

I won\`t go into details about Zend View and it\`s functionality, but to have our template engine we will need to create our View class which will no how to assign variables to smarty and how to render the templates. So in your library extension /Ext which has prefix autoloader, create the directory **View** which will contain file Smarty.php

``` php
/**
 * Smarty template engine integration into Zend Framework
 * Some ideas borrowed from http://devzone.zend.com/article/120
 */

class Ext_View_Smarty extends Zend_View_Abstract
{
    /**
     * Instance of Smarty
     * @var Smarty
     */
    protected $_smarty = null;
    
    /**
     * Template explicitly set to render in this view
     * @var string 
     */
    protected $_customTemplate = '';
    
    /**
     * Smarty config
     * @var array
     */
    private $_config = null;

    /**
     * Class definition and constructor
     *
     * Let's start with the class definition and the constructor part. My class Travello_View_Smarty is extending the Zend_View_Abstract class. In the constructor the parent constructor from Zend_View_Abstract is called first. After that a Smarty object is instantiated, configured and stored in a private attribute.
     * Please note that I use a configuration object from the object store to get the configuration data for Smarty. 
     * 
     * @param array $smartyConfig
     * @param array $config
     */
    public function __construct($smartyConfig, $config = array())
    {
        $this->_config = $smartyConfig;
        parent::__construct($config);        
        $this->_loadSmarty();
    }

    /**
     * Return the template engine object
     *
     * @return Smarty
     */
    public function getEngine()
    {
        return $this->_smarty;
    }
    
    /**
     * Implement _run() method
     *
     * The method _run() is the only method that needs to be implemented in any subclass of Zend_View_Abstract. It is called automatically within the render() method. My implementation just uses the display() method from Smarty to generate and output the template.
     *
     * @param string $template
     */
    protected function _run()
    {
        $file = func_num_args() > 0 && file_exists(func_get_arg(0)) ? func_get_arg(0) : '';
        if ($this->_customTemplate || $file) {
            $template = $this->_customTemplate;
            if (!$template) {
                $template = $file;
            }

            $this->_smarty->display($template);
        } else {
            throw new Zend_View_Exception('Cannot render view without any template being assigned or file does not exist');
        }
    }

    /**
     * Overwrite assign() method
     *
     * The next part is an overwrite of the assign() method from Zend_View_Abstract, which works in a similar way. The big difference is that the values are assigned to the Smarty object and not to the $this->_vars variables array of Zend_View_Abstract.
     *
     * @param string|array $var
     * @return Ext_View_Smarty
     */
    public function assign($var, $value = null)
    {
        if (is_string($var)) {
            $this->_smarty->assign($var, $value);
        } elseif (is_array($var)) {
            foreach ($var as $key => $value) {
                $this->assign($key, $value);
            }
        } else {
            throw new Zend_View_Exception('assign() expects a string or array, got '.gettype($var));
        }
        return $this;
    }

    /**
     * Overwrite escape() method
     *
     * The next part is an overwrite of the escape() method from Zend_View_Abstract. It works both for string and array values and also uses the escape() method from the Zend_View_Abstract. The advantage of this is that I don't have to care about each value of an array to get properly escaped.
     *
     * @param mixed $var
     * @return mixed
     */
    public function escape($var)
    {
        if (is_string($var)) {
            return parent::escape($var);
        } elseif (is_array($var)) {
            foreach ($var as $key => $val) {
                $var[$key] = $this->escape($val);
            }
        }
        return $var;
    }

    /**
     * Print the output
     *
     * The next method output() is a wrapper on the render() method from Zend_View_Abstract. It just sets some headers before printing the output.
     *
     * @param &lt;type> $name
     */
    public function output($name)
    {
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        header("Cache-Control: no-cache");
        header("Pragma: no-cache");
        header("Cache-Control: post-check=0, pre-check=0", false);

        print parent::render($name);
    }

    /**
     * Use Smarty caching
     *
     * The last two methods were created to simply integrate the Smarty caching mechanism in the View class. With the first one you can check for cached template and with the second one you can set the caching on or of.
     *
     * @param string $template
     * @return bool
     */
    public function isCached($template)
    {
        return $this->_smarty->is_cached($template);
    }

    /**
     * Enable/disable caching
     *
     * @param bool $caching
     * @return Ext_View_Smarty
     */
    public function setCaching($caching)
    {
        $this->_smarty->caching = $caching;
        return $this;
    }

    /**
     * Template getter (return file path)
     * @return string
     */
    public function getTemplate()
    {
        return $this->_customTemplate;
    }

    /**
     * Template filename setter
     * @param string
     * @return Ext_View_Smarty
     */
    public function setTemplate($tpl)
    {
        $this->_customTemplate = $tpl;
        return $this;
    }

    /**
     * Magic setter for Zend_View compatibility. Performs assign()
     *
     * @param string $key
     * @param mixed $val
     */
    public function __set($key, $val)
    {
        $this->assign($key, $val);
    }


    /**
     * Magic getter for Zend_View compatibility. Retrieves template var
     *
     * @param string $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->_smarty->getTemplateVars($key);
    }
    
    /**
     * Magic getter for Zend_View compatibility. Removes template var
     * 
     * @see View/Zend_View_Abstract::__unset()
     * @param string $key
     */
    public function __unset($key)
    {
        $this->_smarty->clearAssign($key);
    }
    
    /**
     * Allows testing with empty() and isset() to work
     * Zend_View compatibility. Checks template var for existance
     *
     * @param string $key
     * @return boolean
     */
    public function __isset($key)
    {
        return (null !== $this->_smarty->getTemplateVars($key));
    }

    /**
     * Zend_View compatibility. Retrieves all template vars
     * 
     * @see Zend_View_Abstract::getVars()
     * @return array
     */
    public function getVars()
    {
        return $this->_smarty->getTemplateVars();
    }
    
    /**
     * Updates Smarty's template_dir field with new value
     *
     * @param string $dir
     * @return Ext_View_Smarty
     */
    public function setTemplateDir($dir)
    {
        $this->_smarty->setTemplateDir($dir);
        return $this;
    }
    
    /**
     * Adds another Smarty template_dir to scan for templates
     *
     * @param string $dir
     * @return Ext_View_Smarty
     */
    public function addTemplateDir($dir)
    {
        $this->_smarty->addTemplateDir($dir);
        return $this;
    }
    
    /**
     * Adds another Smarty plugin directory to scan for plugins
     *
     * @param string $dir
     * @return Ext_View_Smarty
     */
    public function addPluginDir($dir)
    {
        $this->_smarty->addPluginsDir($dir);
        return $this;
    }
    
    /**
     * Zend_View compatibility. Removes all template vars
     * 
     * @see View/Zend_View_Abstract::clearVars()
     * @return Ext_View_Smarty
     */
    public function clearVars()
    {
        $this->_smarty->clearAllAssign();
        $this->assign('this', $this);
        return $this;
    }
    
    /**
     * Zend_View compatibility. Add the templates dir
     * 
     * @see View/Zend_View_Abstract::addBasePath()
     * @return Ext_View_Smarty
     */
    public function addBasePath($path, $classPrefix = 'Zend_View')
    {
        parent::addBasePath($path, $classPrefix);
        $this->addScriptPath($path . '/templates');
        $this->addTemplateDir($path . '/templates/static');
        return $this;
    }
    
    /**
     * Zend_View compatibility. Set the templates dir instead of scripts
     * 
     * @see View/Zend_View_Abstract::setBasePath()
     * @return Ext_View_Smarty
     */
    public function setBasePath($path, $classPrefix = 'Zend_View')
    {
        parent::setBasePath($path, $classPrefix);
        $this->setScriptPath($path . '/templates');
        $this->addTemplateDir($path . '/templates/static');
        return $this;
    }
    
    /**
     * Magic clone method, on clone create diferent smarty object
     */
    public function __clone() {
        $this->_loadSmarty();
    }
    
    /**
     * Initializes the smarty and populates config params
     * 
     * @throws Zend_View_Exception
     * @return void
     */
    private function _loadSmarty()
    {
        if (!class_exists('Smarty', true)) {
            require_once 'Smarty/Smarty.class.php';
        }
        
        $this->_smarty = new Smarty();

        if ($this->_config === null) {
            throw new Zend_View_Exception("Could not locate Smarty config - node 'smarty' not found");
        }

        $this->_smarty->caching = $this->_config['caching'];
        $this->_smarty->cache_lifetime = $this->_config['cache_lifetime'];
        $this->_smarty->template_dir = $this->_config['template_dir'];
        $this->_smarty->compile_dir = $this->_config['compile_dir'];
        $this->_smarty->config_dir = $this->_config['config_dir'];
        $this->_smarty->cache_dir = $this->_config['cache_dir'];
        $this->_smarty->left_delimiter = $this->_config['left_delimiter'];
        $this->_smarty->right_delimiter = $this->_config['right_delimiter'];
        $this->assign('this', $this);
    }
}
```

Now the /library directory looks like:

```
...
/myapp
    ...
    /library
        /Smarty
            /plugins
            /sysplugins
            /debug.tpl
            /Smarty.class.php
        /Ext
            /View
                Smarty.php
    /public
...
```

## Bootsraping the Smarty 3 view: {#boot}

Basically we need to bootstrap the view using our new Smarty view and set the layout and script suffixes. Also we need to give this view to the ViewRenderer helper. So lets modify our /application/Bootstrap.php file:

``` php
class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    /**
     * Bootstrap Smarty view
     */
    protected function _initView()
    {
        // initialize smarty view
        $view = new Ext_View_Smarty($this->getOption('smarty'));
        // setup viewRenderer with suffix and view
        $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('ViewRenderer');
        $viewRenderer->setViewSuffix('tpl');
        $viewRenderer->setView($view);
        
        // ensure we have layout bootstraped
        $this->bootstrap('layout');
        // set the tpl suffix to layout also
        $layout = Zend_Layout::getMvcInstance();
        $layout->setViewSuffix('tpl');
        
        return $view;
    }
}
```

## Seeing it in action: {#action}

Now generally all hard work is done and everything else should work as it should..

To see what good do we have lets put some templates on our modules to check if everything is in place and working. First lets have a look at our default module structure:

```
...
/myapp
    /application
        /configs
            application.ini
        /modules
            /default
                /controllers
                    IndexController.php
                    ErrorController.php
                /views
                    /templates
                        /error
                            error.tpl
                        /index
                            index.tpl
                        /static
                            header.tpl
                        layout.tpl
        Bootstrap.php
...
```

Slighly change the index controller:

``` php
<?php
class IndexController extends Zend_Controller_Action
{
    public function indexAction()
    {
        $this->view->hello = 'Hello Smarty 3';
    }
}
```

Create the templates/index/index.tpl

``` html
<p>
  {$hello}
</p>

<p>
  <a href="{$this->url(['controller' => 'index', 'action' => 'index'])}">home</a>
</p>
```

Create the templates/error/error.tpl

``` html
<h1>n error occurred</h1>
  

<h2>{$this->message}</h2>

  {if $this->exception}
<h3>
  Exception information:
</h3>
  

<p>
  <b>Message:</b> {$this->exception->getMessage()}
</p>

  

<h3>
  Stack trace:
</h3>
  

<pre>{$this->exception->getTraceAsString()}
  </pre>

  

<h3>
  Request Parameters:
</h3>
  

<pre>{var_export($this->request->getParams(), true)}
  </pre>
  {/if}
```

Create the templates/layout.tpl

``` html
<div id="header">
  {include file="header.tpl"}
</div>
{$this->layout()->content}
```

Create the templates/static/header.tpl

``` html
this is header
```

Now if everything is properly configured you should see everything as expected

Easy like that, any sugestions on improvements are very welcome

 [1]: http://gediminasm.org/files/smarty.rar "Smarty 3 Zend application"
 [2]: #including-smarty
 [3]: #ini
 [4]: #boot
 [5]: #action
 [6]: http://www.smarty.net/download.php
