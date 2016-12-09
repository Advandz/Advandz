<?php
/**
 * PHP debugging tool, It is an ultimate tool among the diagnostic ones.
 *
 * @package Advandz
 * @subpackage Advandz.debugger
 * @copyright Copyright (c) 2012-2017 CyanDark, Inc. All Rights Reserved.
 * @license https://opensource.org/licenses/MIT The MIT License (MIT)
 * @author The Advandz Team <team@advandz.com>
 */
namespace Tracy\Bridges\Nette;

use Nette;

/**
 * Tracy extension for Nette DI.
 */
class TracyExtension extends Nette\DI\CompilerExtension {
	public $defaults = [
		'email'         => null,
		'fromEmail'     => null,
		'logSeverity'   => null,
		'editor'        => null,
		'browser'       => null,
		'errorTemplate' => null,
		'strictMode'    => null,
		'showBar'       => null,
		'maxLen'        => null,
		'maxDepth'      => null,
		'showLocation'  => null,
		'scream'        => null,
		'bar'           => [], // of class name
		'blueScreen'    => [], // of callback
	];
	/** @var bool */
	private $debugMode;
	/** @var bool */
	private $cliMode;

	public function __construct($debugMode = false, $cliMode = false) {
		$this->debugMode = $debugMode;
		$this->cliMode = $cliMode;
	}

	public function loadConfiguration() {
		$this->validateConfig($this->defaults);
		$builder = $this->getContainerBuilder();

		$builder->addDefinition($this->prefix('logger'))
			->setClass('Tracy\ILogger')
			->setFactory('Tracy\Debugger::getLogger');

		$builder->addDefinition($this->prefix('blueScreen'))
			->setFactory('Tracy\Debugger::getBlueScreen');

		$builder->addDefinition($this->prefix('bar'))
			->setFactory('Tracy\Debugger::getBar');
	}

	public function afterCompile(Nette\PhpGenerator\ClassType $class) {
		$initialize = $class->getMethod('initialize');
		$builder = $this->getContainerBuilder();

		$options = $this->config;
		unset($options['bar'], $options['blueScreen']);
		if (isset($options['logSeverity'])) {
			$res = 0;
			foreach ((array)$options['logSeverity'] as $level) {
				$res |= is_int($level) ? $level : constant($level);
			}
			$options['logSeverity'] = $res;
		}
		foreach ($options as $key => $value) {
			if ($value !== null) {
				$key = ($key === 'fromEmail' ? 'getLogger()->' : '$') . $key;
				$initialize->addBody($builder->formatPhp(
					'Tracy\Debugger::' . $key . ' = ?;',
					Nette\DI\Compiler::filterArguments([$value])
				));
			}
		}

		$logger = $builder->getDefinition($this->prefix('logger'));
		if ($logger->getFactory()->getEntity() !== 'Tracy\Debugger::getLogger') {
			$initialize->addBody($builder->formatPhp('Tracy\Debugger::setLogger(?);', [$logger]));
		}

		if ($this->debugMode) {
			foreach ((array)$this->config['bar'] as $item) {
				$initialize->addBody($builder->formatPhp(
					'$this->getService(?)->addPanel(?);',
					Nette\DI\Compiler::filterArguments([
						$this->prefix('bar'),
						is_string($item) ? new Nette\DI\Statement($item) : $item,
					])
				));
			}

			if (!$this->cliMode) {
				$initialize->addBody('if ($tmp = $this->getByType("Nette\Http\Session", FALSE)) { $tmp->start(); Tracy\Debugger::dispatch(); };');
			}
		}

		foreach ((array)$this->config['blueScreen'] as $item) {
			$initialize->addBody($builder->formatPhp(
				'$this->getService(?)->addPanel(?);',
				Nette\DI\Compiler::filterArguments([$this->prefix('blueScreen'), $item])
			));
		}
	}
}
?>