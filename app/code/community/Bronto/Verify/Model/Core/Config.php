<?php

/**
 * Core Config Model
 *
 * @category  Bronto
 * @package   Bronto_Verify
 * @author    Adam Daniels <adam.daniels@atlanticbt.com>
 * @copyright 2013 Adam Daniels
 * @license   http://www.atlanticbt.com/ Atlantic BT
 */
class Bronto_Verify_Model_Core_Config
    extends Bronto_Verify_Model_Core_Config_Base
{
    //  {{{ properties


    /**
     * Description for const
     */
    const CACHE_TAG = 'CONFIG';

    /**
     * Flag which allow use cache logic
     *
     * @var bool
     * @access protected
     */
    protected $_useCache = false;

    /**
     * Instructions for spitting config cache
     * array(
     *      $sectionName => $recursionLevel
     * )
     * Recursion level provide availability cache subnodes separately
     *
     * @var array
     * @access protected
     */
    protected $_cacheSections = array(
        'admin'     => 0,
        'adminhtml' => 0,
        'crontab'   => 0,
        'install'   => 0,
        'stores'    => 1,
        'websites'  => 0
    );

    /**
     * Loaded Configuration by cached sections
     *
     * @var array
     * @access protected
     */
    protected $_cacheLoadedSections = array();

    /**
     * Configuration options
     *
     * @var Mage_Core_Model_Config_Options
     * @access protected
     */
    protected $_options;

    /**
     * Storage for generated class names
     *
     * @var array
     * @access protected
     */
    protected $_classNameCache = array();

    /**
     * Storage for generated block class names
     *
     * @var array
     */
    protected $_blockClassNameCache = array();

    /**
     * Storage of validated secure urls
     *
     * @var array
     * @access protected
     */
    protected $_secureUrlCache = array();

    /**
     * System environment server variables
     *
     * @var array
     * @access protected
     */
    protected $_distroServerVars;

    /**
     * Array which is using for replace placeholders of server variables
     *
     * @var array
     * @access protected
     */
    protected $_substServerVars;

    /**
     * Resource model
     * Used for operations with DB
     *
     * @var Mage_Core_Model_Mysql4_Config
     * @access protected
     */
    protected $_resourceModel;

    /**
     * Configuration for events by area
     *
     * @var array
     * @access protected
     */
    protected $_eventAreas;

    /**
     * Flag cache for existing or already created directories
     *
     * @var array
     * @access protected
     */
    protected $_dirExists = array();

    /**
     * Flag which allow using cache for config initialization
     *
     * @var bool
     * @access protected
     */
    protected $_allowCacheForInit = true;

    /**
     * Property used during cache save process
     *
     * @var array
     * @access protected
     */
    protected $_cachePartsForSave = array();

    /**
     * Empty configuration object for loading and merging configuration parts
     *
     * @var Mage_Core_Model_Config_Base
     * @access protected
     */
    protected $_prototype;

    /**
     * Flag which identify what local configuration is loaded
     *
     * @var bool
     * @access protected
     */
    protected $_isLocalConfigLoaded = false;

    /**
     * Deprecated properties
     *
     * @deprecated
     * @access protected
     */
    protected $_baseDirCache = array();

    /**
     * @var null
     */
    protected $_customEtcDir = null;

    /**
     * Flag which allow to use modules from local code pool
     *
     * @var bool
     * @access protected
     */
    protected $_canUseLocalModules = null;

    /**
     * Active modules array per namespace
     *
     * @var array
     * @access private
     */
    private $_moduleNamespaces = null;

    /**
     * Modules allowed to load
     * If empty - all modules are allowed
     *
     * @var array
     * @access protected
     */
    protected $_allowedModules = array();

    /**
     * Class construct
     *
     * @param mixed $sourceData
     */
    public function __construct($sourceData = null)
    {
        $this->setCacheId('config_global');
        $this->_options       = new Mage_Core_Model_Config_Options($sourceData);
        $this->_prototype     = new Bronto_Verify_Model_Core_Config_Base();
        $this->_cacheChecksum = null;
        parent::__construct($sourceData);
    }

    /**
     * Get config resource model
     *
     * @return Mage_Core_Model_Mysql4_Config|Object
     */
    public function getResourceModel()
    {
        if (is_null($this->_resourceModel)) {
            $this->_resourceModel = Mage::getResourceModel('bronto_verify/core_config');
        }

        return $this->_resourceModel;
    }

    /**
     * Get configuration options object
     *
     * @return Mage_Core_Model_Config_Options
     * @access public
     */
    public function getOptions()
    {
        return $this->_options;
    }

    /**
     * Set configuration options
     *
     * @param array $options
     *
     * @return Mage_Core_Model_Config
     * @access public
     */
    public function setOptions($options)
    {
        if (is_array($options)) {
            $this->getOptions()->addData($options);
        }

        return $this;
    }

    /**
     * Initialization of core configuration
     *
     * @param array $options
     *
     * @return $this
     */
    public function init($options = array())
    {
        $this->setCacheChecksum(null);
        $this->_cacheLoadedSections = array();
        $this->setOptions($options);
        $this->loadBase();

        $cacheLoad = $this->loadModulesCache();
        if ($cacheLoad) {
            return $this;
        }
        $this->loadModules();
        $this->loadDb();
        $this->saveCache();

        return $this;
    }

    /**
     * Load base system configuration (config.xml and local.xml files)
     *
     * @return Mage_Core_Model_Config
     * @access public
     */
    public function loadBase()
    {
        $etcDir = $this->getOptions()->getEtcDir();
        $files  = glob($etcDir . DS . '*.xml');
        $this->loadFile(current($files));
        while ($file = next($files)) {
            $merge = clone $this->_prototype;
            $merge->loadFile($file);
            $this->extend($merge);
        }
        if (in_array($etcDir . DS . 'local.xml', $files)) {
            $this->_isLocalConfigLoaded = true;
        }

        return $this;
    }

    /**
     * Load cached modules configuration
     *
     * @return bool
     * @access public
     */
    public function loadModulesCache()
    {
        if (Mage::isInstalled(array('etc_dir' => $this->getOptions()->getEtcDir()))) {
            if ($this->_canUseCacheForInit()) {
                Varien_Profiler::start('mage::app::init::config::load_cache');
                $loaded = $this->loadCache();
                Varien_Profiler::stop('mage::app::init::config::load_cache');
                if ($loaded) {
                    $this->_useCache = true;

                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Load modules configuration
     *
     * @return Mage_Core_Model_Config
     * @access public
     */
    public function loadModules()
    {
        Varien_Profiler::start('config/load-modules');
        $this->_loadDeclaredModules();

        $resourceConfig = sprintf('config.%s.xml', $this->_getResourceConnectionModel('core'));
        $mergeToObject  = Mage::getModel('bronto_verify/core_config_base');
        $mergeToObject->setXml($this->getNode());
        $this->loadModulesConfiguration(array('config.xml', $resourceConfig), $mergeToObject);

        /**
         * Prevent local.xml directives overwriting
         */
        $mergeConfig                = clone $this->_prototype;
        $this->_isLocalConfigLoaded = $mergeConfig->loadFile($this->getOptions()->getEtcDir() . DS . 'local.xml');
        if ($this->_isLocalConfigLoaded) {
            $this->extend($mergeConfig);
        }

        $this->applyExtends();
        Varien_Profiler::stop('config/load-modules');

        return $this;
    }

    /**
     * Check if local configuration (DB connection, etc) is loaded
     *
     * @return bool
     * @access public
     */
    public function isLocalConfigLoaded()
    {
        return $this->_isLocalConfigLoaded;
    }

    /**
     * Load config data from DB
     *
     * @return Mage_Core_Model_Config
     * @access public
     */
    public function loadDb()
    {
        if ($this->_isLocalConfigLoaded && Mage::isInstalled()) {
            Varien_Profiler::start('config/load-db');
            $dbConf = $this->getResourceModel();
            $dbConf->loadToXml($this);
            Varien_Profiler::stop('config/load-db');
        }

        return $this;
    }

    /**
     * Reinitialize configuration
     *
     * @param array $options
     *
     * @return Mage_Core_Model_Config
     * @access public
     */
    public function reinit($options = array())
    {
        $this->_allowCacheForInit = false;
        $this->_useCache          = false;

        return $this->init($options);
    }

    /**
     * Check local modules enable/disable flag
     * If local modules are disabled remove local modules path from include dirs
     *
     * @return bool return true if local modules enabled and false if disabled
     * @access protected
     */
    protected function _canUseLocalModules()
    {
        if ($this->_canUseLocalModules !== null) {
            return $this->_canUseLocalModules;
        }

        $disableLocalModules = (string)$this->getNode('global/disable_local_modules');
        if (!empty($disableLocalModules)) {
            $disableLocalModules = (('true' === $disableLocalModules) || ('1' === $disableLocalModules));
        } else {
            $disableLocalModules = false;
        }

        if ($disableLocalModules && !defined('COMPILER_INCLUDE_PATH')) {
            set_include_path(
            // excluded '/app/code/local'
                BP . DS . 'app' . DS . 'code' . DS . 'community' . PS .
                BP . DS . 'app' . DS . 'code' . DS . 'core' . PS .
                BP . DS . 'lib' . PS .
                Mage::registry('original_include_path')
            );
        }
        $this->_canUseLocalModules = !$disableLocalModules;

        return $this->_canUseLocalModules;
    }

    /**
     * Check if cache can be used for config initialization
     *
     * @return bool
     * @access protected
     */
    protected function _canUseCacheForInit()
    {
        return Mage::app()->useCache('config') && $this->_allowCacheForInit
        && !$this->_loadCache($this->_getCacheLockId());
    }

    /**
     * Retrieve cache object
     *
     * @return Zend_Cache_Frontend_File
     * @access public
     */
    public function getCache()
    {
        return Mage::app()->getCache();
    }

    /**
     * Get lock flag cache identifier
     *
     * @return string
     * @access protected
     */
    protected function _getCacheLockId()
    {
        return $this->getCacheId() . '.lock';
    }

    /**
     * Save configuration cache
     *
     * @param array $tags cache tags
     *
     * @return Mage_Core_Model_Config
     * @access public
     */
    public function saveCache($tags = array())
    {
        if (!Mage::app()->useCache('config')) {
            return $this;
        }
        if (!in_array(self::CACHE_TAG, $tags)) {
            $tags[] = self::CACHE_TAG;
        }
        $cacheLockId = $this->_getCacheLockId();
        if ($this->_loadCache($cacheLockId)) {
            return $this;
        }

        if (!empty($this->_cacheSections)) {
            $xml = clone $this->_xml;
            foreach ($this->_cacheSections as $sectionName => $level) {
                $this->_saveSectionCache($this->getCacheId(), $sectionName, $xml, $level, $tags);
                unset($xml->$sectionName);
            }
            $this->_cachePartsForSave[$this->getCacheId()] = $xml->asNiceXml('', false);
        } else {
            return parent::saveCache($tags);
        }

        $this->_saveCache(time(), $cacheLockId, array(), 60);
        $this->removeCache();
        foreach ($this->_cachePartsForSave as $cacheId => $cacheData) {
            $this->_saveCache($cacheData, $cacheId, $tags, $this->getCacheLifetime());
        }
        unset($this->_cachePartsForSave);
        $this->_removeCache($cacheLockId);

        return $this;
    }

    /**
     * Save cache of specified
     *
     * @param                          $idPrefix
     * @param                          $sectionName
     * @param Varien_Simplexml_Element $source
     * @param int                      $recursionLevel
     * @param array                    $tags
     *
     * @return $this
     */
    protected function _saveSectionCache($idPrefix, $sectionName, Varien_Simplexml_Element $source, $recursionLevel = 0, $tags = array())
    {
        if ($source && $source->$sectionName) {
            $cacheId = $idPrefix . '_' . $sectionName;
            if ($recursionLevel > 0) {
                foreach ($source->$sectionName->children() as $subSectionName => $node) {
                    $this->_saveSectionCache(
                        $cacheId, $subSectionName, $source->$sectionName, $recursionLevel - 1, $tags
                    );
                }
            }
            $this->_cachePartsForSave[$cacheId] = $source->$sectionName->asNiceXml('', false);
        }

        return $this;
    }

    /**
     * Load config section cached data
     *
     * @param string $sectionName
     *
     * @return Varien_Simplexml_Element
     * @access protected
     */
    protected function _loadSectionCache($sectionName)
    {
        $cacheId   = $this->getCacheId() . '_' . $sectionName;
        $xmlString = $this->_loadCache($cacheId);

        /**
         * If we can't load section cache (problems with cache storage)
         */
        if (!$xmlString) {
            $this->_useCache = false;
            $this->reinit($this->_options);

            return false;
        } else {
            $xml = simplexml_load_string($xmlString, $this->_elementClass);

            return $xml;
        }
    }

    /**
     * Load cached data by identifier
     *
     * @param string $id
     *
     * @return string
     * @access protected
     */
    protected function _loadCache($id)
    {
        return Mage::app()->loadCache($id);
    }

    /**
     * Save cache data
     *
     * @param string $data
     * @param string $id
     * @param array  $tags
     * @param bool   $lifetime
     *
     * @return bool|Mage_Core_Model_App
     */
    protected function _saveCache($data, $id, $tags = array(), $lifetime = false)
    {
        return Mage::app()->saveCache($data, $id, $tags, $lifetime);
    }

    /**
     * Clear cache data by id
     *
     * @param string $id
     *
     * @return Mage_Core_Model_Config
     * @access protected
     */
    protected function _removeCache($id)
    {
        return Mage::app()->removeCache($id);
    }

    /**
     * Remove configuration cache
     *
     * @return Mage_Core_Model_Config
     * @access public
     */
    public function removeCache()
    {
        Mage::app()->cleanCache(array(self::CACHE_TAG));

        return parent::removeCache();
    }

    /**
     * Configuration cache clean process
     *
     * @return Mage_Core_Model_Config
     * @access public
     */
    public function cleanCache()
    {
        return $this->reinit();
    }

    /**
     * Getter for section configuration object
     *
     * @param array $path
     *
     * @return Mage_Core_Model_Config_Element
     * @access protected
     */
    protected function _getSectionConfig($path)
    {
        $section = $path[0];
        if (!isset($this->_cacheSections[$section])) {
            return false;
        }
        $sectionPath = array_slice($path, 0, $this->_cacheSections[$section] + 1);
        $sectionKey  = implode('_', $sectionPath);

        if (!isset($this->_cacheLoadedSections[$sectionKey])) {
            Varien_Profiler::start('init_config_section:' . $sectionKey);
            $this->_cacheLoadedSections[$sectionKey] = $this->_loadSectionCache($sectionKey);
            Varien_Profiler::stop('init_config_section:' . $sectionKey);
        }

        if ($this->_cacheLoadedSections[$sectionKey] === false) {
            return false;
        }

        return $this->_cacheLoadedSections[$sectionKey];
    }

    /**
     * Get node value from cached section data
     *
     * @param array $path
     *
     * @return Mage_Core_Model_Config
     * @access public
     */
    public function getSectionNode($path)
    {
        $section = $path[0];
        $config  = $this->_getSectionConfig($path);
        $path    = array_slice($path, $this->_cacheSections[$section] + 1);
        if ($config) {
            return $config->descend($path);
        }

        return false;
    }

    /**
     * Returns node found by the $path and scope info
     *
     * @param string $path
     * @param string $scope
     * @param string $scopeCode
     *
     * @return Mage_Core_Model_Config_Element
     * @access public
     */
    public function getNode($path = null, $scope = '', $scopeCode = null)
    {
        if ($scope !== '') {
            if (('store' === $scope) || ('website' === $scope)) {
                $scope .= 's';
            }
            if (('default' !== $scope) && is_int($scopeCode)) {
                if ('stores' == $scope) {
                    $scopeCode = Mage::app()->getStore($scopeCode)->getCode();
                } elseif ('websites' == $scope) {
                    $scopeCode = Mage::app()->getWebsite($scopeCode)->getCode();
                } else {
                    Mage::throwException(Mage::helper('core')->__('Unknown scope "%s".', $scope));
                }
            }
            $path = $scope . ($scopeCode ? '/' . $scopeCode : '') . (empty($path) ? '' : '/' . $path);
        }

        /**
         * Check path cache loading
         */
        if ($this->_useCache && ($path !== null)) {
            $path    = explode('/', $path);
            $section = $path[0];
            if (isset($this->_cacheSections[$section])) {
                $res = $this->getSectionNode($path);
                if ($res !== false) {
                    return $res;
                }
            }
        }

        return parent::getNode($path);
    }

    /**
     * Create node by $path and set its value.
     *
     * @param string $path separated by slashes
     * @param string $value
     * @param bool   $overwrite
     *
     * @return Varien_Simplexml_Config
     * @access public
     */
    public function setNode($path, $value, $overwrite = true)
    {
        if ($this->_useCache && ($path !== null)) {
            $sectionPath = explode('/', $path);
            $config      = $this->_getSectionConfig($sectionPath);
            if ($config) {
                $sectionPath = array_slice($sectionPath, $this->_cacheSections[$sectionPath[0]] + 1);
                $sectionPath = implode('/', $sectionPath);
                $config->setNode($sectionPath, $value, $overwrite);
            }
        }

        return parent::setNode($path, $value, $overwrite);
    }

    /**
     * Retrieve Declared Module file list
     *
     * @return array
     * @access protected
     */
    protected function _getDeclaredModuleFiles()
    {
        $etcDir      = $this->getOptions()->getEtcDir();
        $moduleFiles = glob($etcDir . DS . 'modules' . DS . '*.xml');

        if (!$moduleFiles) {
            return false;
        }

        $collectModuleFiles = array(
            'base'   => array(),
            'mage'   => array(),
            'custom' => array()
        );

        foreach ($moduleFiles as $v) {
            $name = explode(DIRECTORY_SEPARATOR, $v);
            $name = substr($name[count($name) - 1], 0, -4);

            if ($name == 'Mage_All') {
                $collectModuleFiles['base'][] = $v;
            } else if (substr($name, 0, 5) == 'Mage_') {
                $collectModuleFiles['mage'][] = $v;
            } else {
                $collectModuleFiles['custom'][] = $v;
            }
        }

        return array_merge(
            $collectModuleFiles['base'],
            $collectModuleFiles['mage'],
            $collectModuleFiles['custom']
        );
    }

    /**
     * Add module(s) to allowed list
     *
     * @param string|array $module
     *
     * @return Mage_Core_Model_Config
     * @access public
     */
    public function addAllowedModules($module)
    {
        if (is_array($module)) {
            foreach ($module as $moduleName) {
                $this->addAllowedModules($moduleName);
            }
        } elseif (!in_array($module, $this->_allowedModules)) {
            $this->_allowedModules[] = $module;
        }

        return $this;
    }

    /**
     * Define if module is allowed
     *
     * @param string $moduleName
     *
     * @return bool
     * @access protected
     */
    protected function _isAllowedModule($moduleName)
    {
        if (empty($this->_allowedModules)) {
            return true;
        } else {
            return in_array($moduleName, $this->_allowedModules);
        }
    }

    /**
     * Load declared modules configuration
     *
     * @param null $mergeConfig deprecated
     *
     * @return Mage_Core_Model_Config
     * @access protected
     */
    protected function _loadDeclaredModules($mergeConfig = null)
    {
        $moduleFiles = $this->_getDeclaredModuleFiles();
        if (!$moduleFiles) {
            return false;
        }

        Varien_Profiler::start('config/load-modules-declaration');

        $unsortedConfig = new Bronto_Verify_Model_Core_Config_Base();
        $unsortedConfig->loadString('<config/>');
        $fileConfig = new Bronto_Verify_Model_Core_Config_Base();

        // load modules declarations
        foreach ($moduleFiles as $file) {
            $fileConfig->loadFile($file);
            $unsortedConfig->extend($fileConfig);
        }

        $moduleDepends = array();
        foreach ($unsortedConfig->getNode('modules')->children() as $moduleName => $moduleNode) {
            if (!$this->_isAllowedModule($moduleName)) {
                continue;
            }

            $depends = array();
            if ($moduleNode->depends) {
                foreach ($moduleNode->depends->children() as $depend) {
                    $depends[$depend->getName()] = true;
                }
            }
            $moduleDepends[$moduleName] = array(
                'module'  => $moduleName,
                'depends' => $depends,
                'active'  => ('true' === (string)$moduleNode->active ? true : false),
            );
        }

        // check and sort module dependence
        $moduleDepends = $this->_sortModuleDepends($moduleDepends);

        // create sorted config
        $sortedConfig = new Bronto_Verify_Model_Core_Config_Base();
        $sortedConfig->loadString('<config><modules/></config>');

        foreach ($unsortedConfig->getNode()->children() as $nodeName => $node) {
            if ($nodeName != 'modules') {
                $sortedConfig->getNode()->appendChild($node);
            }
        }

        foreach ($moduleDepends as $moduleProp) {
            $node = $unsortedConfig->getNode('modules/' . $moduleProp['module']);
            $sortedConfig->getNode('modules')->appendChild($node);
        }

        $this->extend($sortedConfig);

        Varien_Profiler::stop('config/load-modules-declaration');

        return $this;
    }

    /**
     * Sort modules and check depends
     *
     * @param array $modules
     *
     * @return array
     * @access protected
     */
    protected function _sortModuleDepends($modules)
    {
        foreach ($modules as $moduleName => $moduleProps) {
            $depends = $moduleProps['depends'];
            foreach ($moduleProps['depends'] as $depend => $true) {
                if ($moduleProps['active'] && ((!isset($modules[$depend])) || empty($modules[$depend]['active']))) {
                    Mage::throwException(
                        Mage::helper('core')->__('Module "%1$s" requires module "%2$s".', $moduleName, $depend)
                    );
                }
                $depends = array_merge($depends, $modules[$depend]['depends']);
            }
            $modules[$moduleName]['depends'] = $depends;
        }
        $modules = array_values($modules);

        $size = count($modules) - 1;
        for ($i = $size; $i >= 0; $i--) {
            for ($j = $size; $i < $j; $j--) {
                if (isset($modules[$i]['depends'][$modules[$j]['module']])) {
                    $value       = $modules[$i];
                    $modules[$i] = $modules[$j];
                    $modules[$j] = $value;
                }
            }
        }

        $definedModules = array();
        foreach ($modules as $moduleProp) {
            foreach ($moduleProp['depends'] as $dependModule => $true) {
                if (!isset($definedModules[$dependModule])) {
                    Mage::throwException(
                        Mage::helper('core')->__(
                            'Module "%1$s" cannot depend on "%2$s".', $moduleProp['module'], $dependModule
                        )
                    );
                }
            }
            $definedModules[$moduleProp['module']] = true;
        }

        return $modules;
    }

    /**
     * Determine whether provided name begins from any available modules, according to namespaces priority
     * If matched, returns as the matched module "factory" name or a fully qualified module name
     *
     * @param string $name
     * @param bool   $asFullModuleName
     *
     * @return string
     * @access public
     */
    public function determineOmittedNamespace($name, $asFullModuleName = false)
    {
        if (null === $this->_moduleNamespaces) {
            $this->_moduleNamespaces = array();
            foreach ($this->_xml->xpath('modules/*') as $m) {
                if ((string)$m->active == 'true') {
                    $moduleName                                                                 = $m->getName();
                    $module                                                                     = strtolower($moduleName);
                    $this->_moduleNamespaces[substr($module, 0, strpos($module, '_'))][$module] = $moduleName;
                }
            }
        }

        $name                 = explode('_', strtolower($name));
        $partsNum             = count($name);
        $defaultNamespaceFlag = false;
        foreach ($this->_moduleNamespaces as $namespaceName => $namespace) {
            // assume the namespace is omitted (default namespace only, which comes first)
            if ($defaultNamespaceFlag === false) {
                $defaultNamespaceFlag = true;
                $defaultNS            = $namespaceName . '_' . $name[0];
                if (isset($namespace[$defaultNS])) {
                    return $asFullModuleName ? $namespace[$defaultNS] : $name[0]; // return omitted as well
                }
            }
            // assume namespace is qualified
            if (isset($name[1])) {
                $fullNS = $name[0] . '_' . $name[1];
                if (2 <= $partsNum && isset($namespace[$fullNS])) {
                    return $asFullModuleName ? $namespace[$fullNS] : $fullNS;
                }
            }
        }

        return '';
    }

    /**
     * Iterate all active modules "etc" folders and combine data from
     * specified xml file name to one object
     *
     * @param                             $fileName
     * @param Mage_Core_Model_Config_Base $mergeToObject
     * @param null                        $mergeModel
     *
     * @return Bronto_Verify_Model_Core_Config_Base|Mage_Core_Model_Config_Base
     */
    public function loadModulesConfiguration($fileName, Bronto_Verify_Model_Core_Config_Base $mergeToObject = null, $mergeModel = null)
    {
        $disableLocalModules = !$this->_canUseLocalModules();

        if ($mergeToObject === null) {
            $mergeToObject = clone $this->_prototype;
            $mergeToObject->loadString('<config/>');
        }
        if ($mergeModel === null) {
            $mergeModel = clone $this->_prototype;
        }
        $modules = $this->getNode('modules')->children();
        foreach ($modules as $modName => $module) {
            if ($module->is('active')) {
                if ($disableLocalModules && ('local' === (string)$module->codePool)) {
                    continue;
                }
                if (!is_array($fileName)) {
                    $fileName = array($fileName);
                }

                switch ((string)$module->codePool) {
                    case 'local' :
                    case 'community' :
                        Mage::register('conflict_datastore_enabled', true);
                        break;

                    default :
                        Mage::register('conflict_datastore_enabled', false);
                        break;
                }

                foreach ($fileName as $configFile) {
                    $configFile = $this->getModuleDir('etc', $modName) . DS . $configFile;
                    if ($mergeModel->loadFile($configFile)) {
                        Mage::register('conflict_datastore_config_file', $configFile);
                        $mergeToObject->extend($mergeModel, true);
                        Mage::unregister('conflict_datastore_config_file');
                    }
                }
                Mage::unregister('conflict_datastore_enabled');
            }
        }

        return $mergeToObject;
    }

    /**
     * Retrieve temporary directory path
     *
     * @return string
     * @access public
     */
    public function getTempVarDir()
    {
        return $this->getOptions()->getVarDir();
    }

    /**
     * Get default server variables values
     *
     * @return array
     * @access public
     */
    public function getDistroServerVars()
    {
        if (!$this->_distroServerVars) {

            if (isset($_SERVER['SCRIPT_NAME']) && isset($_SERVER['HTTP_HOST'])) {
                $secure = (!empty($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] != 'off')) || $_SERVER['SERVER_PORT'] == '443';
                $scheme = ($secure ? 'https' : 'http') . '://';

                $hostArr = explode(':', $_SERVER['HTTP_HOST']);
                $host    = $hostArr[0];
                $port    = isset(
                $hostArr[1]) && (!$secure && $hostArr[1] != 80 || $secure && $hostArr[1] != 443
                ) ? ':' . $hostArr[1] : '';
                $path    = Mage::app()->getRequest()->getBasePath();

                $baseUrl = $scheme . $host . $port . rtrim($path, '/') . '/';
            } else {
                $baseUrl = 'http://localhost/';
            }

            $options                 = $this->getOptions();
            $this->_distroServerVars = array(
                'root_dir' => $options->getBaseDir(),
                'app_dir'  => $options->getAppDir(),
                'var_dir'  => $options->getVarDir(),
                'base_url' => $baseUrl,
            );

            foreach ($this->_distroServerVars as $k => $v) {
                $this->_substServerVars['{{' . $k . '}}'] = $v;
            }
        }

        return $this->_distroServerVars;
    }

    /**
     * @param $data
     *
     * @return mixed
     */
    public function substDistroServerVars($data)
    {
        $this->getDistroServerVars();

        return str_replace(
            array_keys($this->_substServerVars),
            array_values($this->_substServerVars),
            $data
        );
    }

    /**
     * Get module config node
     *
     * @param string $moduleName
     *
     * @return Varien_Simplexml_Element
     * @access public
     */
    public function getModuleConfig($moduleName = '')
    {
        $modules = $this->getNode('modules');
        if ('' === $moduleName) {
            return $modules;
        } else {
            return $modules->$moduleName;
        }
    }

    /**
     * Get module setup class instance.
     *
     * Defaults to Mage_Core_Setup
     *
     * @param string|Varien_Simplexml_Element $module
     *
     * @return object
     * @access public
     */
    public function getModuleSetup($module = '')
    {
        $className = 'Mage_Core_Setup';
        if ('' !== $module) {
            if (is_string($module)) {
                $module = $this->getModuleConfig($module);
            }
            if (isset($module->setup)) {
                $moduleClassName = $module->setup->getClassName();
                if (!empty($moduleClassName)) {
                    $className = $moduleClassName;
                }
            }
        }

        return new $className($module);
    }

    /**
     * Get base filesystem directory. depends on $type
     *
     * If $moduleName is specified retrieves specific value for the module.
     *
     * @param string $type
     *
     * @deprecated in favor of Mage_Core_Model_Config_Options
     * @todo       get global dir config
     * @return string
     * @access     public
     */
    public function getBaseDir($type = 'base')
    {
        return $this->getOptions()->getDir($type);
    }

    /**
     * Get temporary data directory name
     *
     * @param string $path
     * @param string $type
     *
     * @return string
     * @access public
     */
    public function getVarDir($path = null, $type = 'var')
    {
        $dir = Mage::getBaseDir($type) . ($path !== null ? DS . $path : '');
        if (!$this->createDirIfNotExists($dir)) {
            return false;
        }

        return $dir;
    }

    /**
     * @param $dir
     *
     * @return bool
     */
    public function createDirIfNotExists($dir)
    {
        return $this->getOptions()->createDirIfNotExists($dir);
    }

    /**
     * Get module directory by directory type
     *
     * @param string $type
     * @param string $moduleName
     *
     * @return string
     * @access public
     */
    public function getModuleDir($type, $moduleName)
    {
        $codePool = (string)$this->getModuleConfig($moduleName)->codePool;
        $dir      = $this->getOptions()->getCodeDir() . DS . $codePool . DS . uc_words($moduleName, DS);

        switch ($type) {
            case 'etc':
                $dir .= DS . 'etc';
                break;

            case 'controllers':
                $dir .= DS . 'controllers';
                break;

            case 'sql':
                $dir .= DS . 'sql';
                break;
            case 'data':
                $dir .= DS . 'data';
                break;

            case 'locale':
                $dir .= DS . 'locale';
                break;
        }

        $dir = str_replace('/', DS, $dir);

        return $dir;
    }

    /**
     * Load event observers for an area (front, admin)
     *
     * @param string $area
     *
     * @return boolean
     * @access public
     */
    public function loadEventObservers($area)
    {
        $events = $this->getNode("$area/events");
        if ($events) {
            $events = $events->children();
        } else {
            return false;
        }

        foreach ($events as $event) {
            $eventName = $event->getName();
            $observers = $event->observers->children();
            foreach ($observers as $observer) {
                switch ((string)$observer->type) {
                    case 'singleton':
                        $callback = array(
                            Mage::getSingleton((string)$observer->class),
                            (string)$observer->method
                        );
                        break;
                    case 'object':
                    case 'model':
                        $callback = array(
                            Mage::getModel((string)$observer->class),
                            (string)$observer->method
                        );
                        break;
                    default:
                        $callback = array($observer->getClassName(), (string)$observer->method);
                        break;
                }

                $args          = (array)$observer->args;
                $observerClass = $observer->observer_class ? (string)$observer->observer_class : '';
                Mage::addObserver($eventName, $callback, $args, $observer->getName(), $observerClass);
            }
        }

        return true;
    }

    /**
     * Get standard path variables.
     *
     * To be used in blocks, templates, etc.
     *
     * @param array|string $args Module name if string
     *
     * @return array
     * @access public
     */
    public function getPathVars($args = null)
    {
        $path = array();

        $path['baseUrl']       = Mage::getBaseUrl();
        $path['baseSecureUrl'] = Mage::getBaseUrl('link', true);

        return $path;
    }

    /**
     * Retrieve class name by class group
     *
     * @param string $groupType     currently supported model, block, helper
     * @param string $classId       slash separated class identifier, ex. group/class
     * @param string $groupRootNode optional config path for group config
     *
     * @return string
     * @access public
     */
    public function getGroupedClassName($groupType, $classId, $groupRootNode = null)
    {
        if (empty($groupRootNode)) {
            $groupRootNode = 'global/' . $groupType . 's';
        }

        $classArr = explode('/', trim($classId));
        $group    = $classArr[0];
        $class    = !empty($classArr[1]) ? $classArr[1] : null;

        if (isset($this->_classNameCache[$groupRootNode][$group][$class])) {
            return $this->_classNameCache[$groupRootNode][$group][$class];
        }

        $config = $this->_xml->global->{$groupType . 's'}->{$group};

        // First - check maybe the entity class was rewritten
        $className = null;
        if (isset($config->rewrite->$class)) {
            $className = (string)$config->rewrite->$class;
        } else {
            /**
             * Backwards compatibility for pre-MMDB extensions.
             * In MMDB release resource nodes <..._mysql4> were renamed to <..._resource>. So <deprecatedNode> is left
             * to keep name of previously used nodes, that still may be used by non-updated extensions.
             */
            if ($config->deprecatedNode) {
                $deprecatedNode = $config->deprecatedNode;
                $configOld      = $this->_xml->global->{$groupType . 's'}->$deprecatedNode;
                if (isset($configOld->rewrite->$class)) {
                    $className = (string)$configOld->rewrite->$class;
                }
            }
        }

        // Second - if entity is not rewritten then use class prefix to form class name
        if (empty($className)) {
            if (!empty($config)) {
                $className = $config->getClassName();
            }
            if (empty($className)) {
                $className = 'mage_' . $group . '_' . $groupType;
            }
            if (!empty($class)) {
                $className .= '_' . $class;
            }
            $className = uc_words($className);
        }

        $this->_classNameCache[$groupRootNode][$group][$class] = $className;

        return $className;
    }

    /**
     * Retrieve block class name
     *
     * @param string $blockType
     *
     * @return string
     * @access public
     */
    public function getBlockClassName($blockType)
    {
        if (strpos($blockType, '/') === false) {
            return $blockType;
        }

        return $this->getGroupedClassName('block', $blockType);
    }

    /**
     * Retrieve helper class name
     *
     * @param $helperName
     *
     * @return string
     */
    public function getHelperClassName($helperName)
    {
        if (strpos($helperName, '/') === false) {
            $helperName .= '/data';
        }

        return $this->getGroupedClassName('helper', $helperName);
    }

    /**
     * Retrieve resource helper instance
     *
     * Example:
     * $config->getResourceHelper('cms')
     * will instantiate Mage_Cms_Model_Resource_Helper_<db_adapter_name>
     *
     * @param string $moduleName
     *
     * @return Mage_Core_Model_Resource_Helper_Abstract|false
     * @access public
     */
    public function getResourceHelper($moduleName)
    {
        $connectionModel = $this->_getResourceConnectionModel($moduleName);
        $helperClass     = sprintf('%s/helper_%s', $moduleName, $connectionModel);
        $helperClassName = $this->_getResourceModelFactoryClassName($helperClass);
        if ($helperClassName) {
            return $this->getModelInstance($helperClassName, $moduleName);
        }

        return false;
    }

    /**
     * Retrieve module class name
     *
     * @param string $modelClass
     *
     * @return string
     * @access public
     */
    public function getModelClassName($modelClass)
    {
        $modelClass = trim($modelClass);
        if (strpos($modelClass, '/') === false) {
            return $modelClass;
        }

        return $this->getGroupedClassName('model', $modelClass);
    }

    /**
     * Get model class instance.
     *
     * Example:
     * $config->getModelInstance('catalog/product')
     *
     * Will instantiate Mage_Catalog_Model_Mysql4_Product
     *
     * @param string       $modelClass
     * @param array|object $constructArguments
     *
     * @return Mage_Core_Model_Abstract
     * @access public
     */
    public function getModelInstance($modelClass = '', $constructArguments = array())
    {
        $className = $this->getModelClassName($modelClass);
        if (class_exists($className)) {
            Varien_Profiler::start('CORE::create_object_of::' . $className);
            $obj = new $className($constructArguments);
            Varien_Profiler::stop('CORE::create_object_of::' . $className);

            return $obj;
        } else {
            return false;
        }
    }

    /**
     * @param $path
     *
     * @return bool
     */
    public function getNodeClassInstance($path)
    {
        $config = Mage::getConfig()->getNode($path);
        if (!$config) {
            return false;
        } else {
            $className = $config->getClassName();

            return new $className();
        }
    }

    /**
     * Get resource model object by alias
     *
     * @param string $modelClass
     * @param array  $constructArguments
     *
     * @return object
     * @access public
     */
    public function getResourceModelInstance($modelClass = '', $constructArguments = array())
    {
        $factoryName = $this->_getResourceModelFactoryClassName($modelClass);
        if (!$factoryName) {
            return false;
        }

        return $this->getModelInstance($factoryName, $constructArguments);
    }

    /**
     * Get resource configuration for resource name
     *
     * @param string $name
     *
     * @return Varien_Simplexml_Element
     * @access public
     */
    public function getResourceConfig($name)
    {
        return $this->_xml->global->resources->{$name};
    }

    /**
     * Get connection configuration
     *
     * @param string $name
     *
     * @return Varien_Simplexml_Element
     * @access public
     */
    public function getResourceConnectionConfig($name)
    {
        $config = $this->getResourceConfig($name);
        if ($config) {
            $conn = $config->connection;
            if ($conn) {
                if (!empty($conn->use)) {
                    return $this->getResourceConnectionConfig((string)$conn->use);
                } else {
                    return $conn;
                }
            }
        }

        return false;
    }

    /**
     * Retrieve resource type configuration for resource name
     *
     * @param string $type
     *
     * @return Varien_Simplexml_Element
     * @access public
     */
    public function getResourceTypeConfig($type)
    {
        return $this->_xml->global->resource->connection->types->{$type};
    }

    /**
     * Retrieve store Ids for $path with checking
     *
     * if empty $allowValues then retrieve all stores values
     *
     * return array($storeId=>$pathValue)
     *
     * @param        $path
     * @param array  $allowValues
     * @param string $useAsKey
     *
     * @return array
     */
    public function getStoresConfigByPath($path, $allowValues = array(), $useAsKey = 'id')
    {
        $storeValues = array();
        $stores      = $this->getNode('stores');
        foreach ($stores->children() as $code => $store) {
            switch ($useAsKey) {
                case 'id':
                    $key = (int)$store->descend('system/store/id');
                    break;

                case 'code':
                    $key = $code;
                    break;

                case 'name':
                    $key = (string)$store->descend('system/store/name');
            }
            if ($key === false) {
                continue;
            }

            $pathValue = (string)$store->descend($path);

            if (empty($allowValues)) {
                $storeValues[$key] = $pathValue;
            } else if (in_array($pathValue, $allowValues)) {
                $storeValues[$key] = $pathValue;
            }
        }

        return $storeValues;
    }

    /**
     * Check security requirements for url
     *
     * @param string $url
     *
     * @return bool
     * @access public
     */
    public function shouldUrlBeSecure($url)
    {
        if (!isset($this->_secureUrlCache[$url])) {
            $this->_secureUrlCache[$url] = false;
            $secureUrls                  = $this->getNode('frontend/secure_url');
            foreach ($secureUrls->children() as $match) {
                if (strpos($url, (string)$match) === 0) {
                    $this->_secureUrlCache[$url] = true;
                    break;
                }
            }
        }

        return $this->_secureUrlCache[$url];
    }

    /**
     * Get DB table names prefix
     *
     * @return string
     * @access public
     */
    public function getTablePrefix()
    {
        return $this->_xml->global->resources->db->table_prefix;
    }

    /**
     * Get events configuration
     *
     * @param string $area      event area
     * @param string $eventName event name
     *
     * @return Mage_Core_Model_Config_Element
     * @access public
     */
    public function getEventConfig($area, $eventName)
    {
        //return $this->getNode($area)->events->{$eventName};
        if (!isset($this->_eventAreas[$area])) {
            $this->_eventAreas[$area] = $this->getNode($area)->events;
        }

        return $this->_eventAreas[$area]->{$eventName};
    }

    /**
     * Save config value to DB
     *
     * @param        $path
     * @param        $value
     * @param string $scope
     * @param int    $scopeId
     *
     * @return $this
     */
    public function saveConfig($path, $value, $scope = 'default', $scopeId = 0)
    {
        $resource = $this->getResourceModel();
        $resource->saveConfig(rtrim($path, '/'), $value, $scope, $scopeId);

        return $this;
    }

    /**
     * Delete config value from DB
     *
     * @param string $path
     * @param string $scope
     * @param int    $scopeId
     *
     * @return Mage_Core_Model_Config
     * @access public
     */
    public function deleteConfig($path, $scope = 'default', $scopeId = 0)
    {
        $resource = $this->getResourceModel();
        $resource->deleteConfig(rtrim($path, '/'), $scope, $scopeId);

        return $this;
    }

    /**
     * Get fieldset from configuration
     *
     * @param string $name fieldset name
     * @param string $root fieldset area, could be 'admin'
     *
     * @return null|array
     * @access public
     */
    public function getFieldset($name, $root = 'global')
    {
        $rootNode = $this->getNode($root . '/fieldsets');
        if (!$rootNode) {
            return null;
        }

        return $rootNode->$name ? $rootNode->$name->children() : null;
    }

    /**
     * Retrieve resource connection model name
     *
     * @param string $moduleName
     *
     * @return string
     * @access protected
     */
    protected function _getResourceConnectionModel($moduleName = null)
    {
        $config = null;
        if (!is_null($moduleName)) {
            $setupResource = $moduleName . '_setup';
            $config        = $this->getResourceConnectionConfig($setupResource);
        }
        if (!$config) {
            $config = $this->getResourceConnectionConfig(Mage_Core_Model_Resource::DEFAULT_SETUP_RESOURCE);
        }

        return (string)$config->model;
    }

    /**
     * Get factory class name for a resource
     *
     * @param string $modelClass
     *
     * @return string|false
     * @access protected
     */
    protected function _getResourceModelFactoryClassName($modelClass)
    {
        $classArray = explode('/', $modelClass);
        if (count($classArray) != 2) {
            return false;
        }

        list($module, $model) = $classArray;
        if (!isset($this->_xml->global->models->{$module})) {
            return false;
        }

        $moduleNode = $this->_xml->global->models->{$module};
        if (!empty($moduleNode->resourceModel)) {
            $resourceModel = (string)$moduleNode->resourceModel;
        } else {
            return false;
        }

        return $resourceModel . '/' . $model;
    }

    /**
     * Get a resource model class name
     *
     * @param string $modelClass
     *
     * @return string|false
     * @access public
     */
    public function getResourceModelClassName($modelClass)
    {
        $factoryName = $this->_getResourceModelFactoryClassName($modelClass);
        if ($factoryName) {
            return $this->getModelClassName($factoryName);
        }

        return false;
    }
}
