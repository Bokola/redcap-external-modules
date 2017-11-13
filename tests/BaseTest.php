<?php
namespace ExternalModules;
require_once dirname(__FILE__) . '/../classes/ExternalModules.php';

use PHPUnit\Framework\TestCase;
use \Exception;

const TEST_MODULE_PREFIX = ExternalModules::TEST_MODULE_PREFIX;
const TEST_MODULE_VERSION = 'v1.0.0';
const TEST_SETTING_KEY = 'unit-test-setting-key';
const FILE_SETTING_KEY = 'unit-test-file-setting-key';
const TEST_SETTING_PID = 1;

abstract class BaseTest extends TestCase
{
	protected $backupGlobals = FALSE;

	private $testModuleInstance;

	public static function setUpBeforeClass(){
		// These were added simply to avoid warnings from REDCap code.
		$_SERVER['REMOTE_ADDR'] = 'unit testing';
		if(!defined('PAGE')){
			define('PAGE', 'unit testing');
		}

		ExternalModules::initialize();
	}

	protected function setUp(){
		self::cleanupSettings();
	}

	protected function tearDown()
	{
		self::cleanupSettings();
	}

	private function cleanupSettings()
	{
		$this->setConfig([]);
		$this->getInstance()->testHookArguments = null;

		$this->removeSystemSetting();
		$this->removeProjectSetting();

		$m = self::getInstance();
		$m->removeSystemSetting(ExternalModules::KEY_VERSION, TEST_SETTING_PID);
		$m->removeSystemSetting(ExternalModules::KEY_ENABLED, TEST_SETTING_PID);
		$m->removeProjectSetting(ExternalModules::KEY_ENABLED, TEST_SETTING_PID);

		$_GET = [];
		$_POST = [];
	}

	protected function setSystemSetting($value)
	{
		self::getInstance()->setSystemSetting(TEST_SETTING_KEY, $value);
	}

	protected function getSystemSetting()
	{
		return self::getInstance()->getSystemSetting(TEST_SETTING_KEY);
	}

	protected function removeSystemSetting()
	{
		self::getInstance()->removeSystemSetting(TEST_SETTING_KEY);
	}

	protected function setProjectSetting($value)
	{
		self::getInstance()->setProjectSetting(TEST_SETTING_KEY, $value, TEST_SETTING_PID);
	}

	protected function getProjectSetting()
	{
		return self::getInstance()->getProjectSetting(TEST_SETTING_KEY, TEST_SETTING_PID);
	}

	protected function removeProjectSetting()
	{
		self::getInstance()->removeProjectSetting(TEST_SETTING_KEY, TEST_SETTING_PID);
	}

	protected function getInstance()
	{
		if($this->testModuleInstance == null){
			$instance = new BaseTestExternalModule();
			$this->setExternalModulesProperty('instanceCache', [TEST_MODULE_PREFIX => [TEST_MODULE_VERSION => $instance]]);

			$this->testModuleInstance = $instance;
		}

		return $this->testModuleInstance;
	}

	protected function setConfig($config)
	{
		$this->setExternalModulesProperty('configs', [TEST_MODULE_PREFIX => [TEST_MODULE_VERSION => $config]]);
		$this->setExternalModulesProperty('systemwideEnabledVersions', [TEST_MODULE_PREFIX => TEST_MODULE_VERSION]);
	}

	private function setExternalModulesProperty($name, $value)
	{
		$externalModulesClass = new \ReflectionClass("ExternalModules\\ExternalModules");
		$configsProperty = $externalModulesClass->getProperty($name);
		$configsProperty->setAccessible(true);
		$configsProperty->setValue($value);
	}

	protected function assertThrowsException($callable, $exceptionExcerpt)
	{
		$exceptionThrown = false;
		try{
			$callable();
		}
		catch(Exception $e){
			if(empty($exceptionExcerpt)){
				throw new Exception('You must specify an exception excerpt!  Here\'s a hint: ' . $e->getMessage());
			}
			else if(strpos($e->getMessage(), $exceptionExcerpt) === false){
				throw new Exception("Could not find the string '$exceptionExcerpt' in the following exception message: " . $e->getMessage());
			}

			$exceptionThrown = true;
		}

		$this->assertTrue($exceptionThrown);
	}

	protected function callPrivateMethod($methodName)
	{
		$args = func_get_args();
		array_unshift($args, $this->getReflectionClass());

		return call_user_func_array([$this, 'callPrivateMethodForClass'], $args);
	}

	protected function callPrivateMethodForClass($classInstanceOrName, $methodName)
	{
		if(gettype($classInstanceOrName) == 'string'){
			$instance = null;
		}
		else{
			$instance = $classInstanceOrName;
		}

		$args = func_get_args();
		array_shift($args); // remove the $classInstanceOrName
		array_shift($args); // remove the $methodName

		$class = new \ReflectionClass($classInstanceOrName);
		$method = $class->getMethod($methodName);
		$method->setAccessible(true);

		return $method->invokeArgs($instance, $args);
	}

	protected function getPrivateVariable($name)
	{
		$class = $this->getReflectionClass();
		$property = $class->getProperty($name);
		$property->setAccessible(true);

		return $property->getValue($this->getReflectionClass());
	}

	protected abstract function getReflectionClass();
}

class BaseTestExternalModule extends AbstractExternalModule {

	public $testHookArguments;
	private $settingKeyPrefix;

	function __construct()
	{
		$this->PREFIX = TEST_MODULE_PREFIX;
		$this->VERSION = TEST_MODULE_VERSION;

		parent::__construct();
	}

	function getModuleDirectoryName()
	{
		return ExternalModules::getModuleDirectoryPath($this->PREFIX, $this->VERSION);
	}

	function __call($name, $arguments)
	{
		// We end up in here when we try to call a private method.
		// use reflection to call the method anyway (allowing unit testing of private methods).
		$method = new \ReflectionMethod(get_class(), $name);
		$method->setAccessible(true);

		return $method->invokeArgs ($this, $arguments);
	}

	function hook_test_delay($delayTestFunction)
	{
        $delayTestFunction($this->delayModuleExecution());
	}

	function hook_test()
	{
		$this->testHookArguments = func_get_args();
	}

	protected function getSettingKeyPrefix()
	{
		if($this->settingKeyPrefix){
			return $this->settingKeyPrefix;
		}
		else{
			return parent::getSettingKeyPrefix();
		}
	}

	function setSettingKeyPrefix($settingKeyPrefix)
	{
		$this->settingKeyPrefix = $settingKeyPrefix;
	}
}
