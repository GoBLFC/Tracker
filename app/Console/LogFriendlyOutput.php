<?php

namespace App\Console;

use Symfony\Component\Console\Input\InputOption;

trait LogFriendlyOutput {
	public function __construct() {
		parent::__construct();
		$this->getDefinition()->addOption(new InputOption(
			'loggable',
			null,
			InputOption::VALUE_NONE,
			'Prepend a timestamp and verbosity to each line of output',
			null,
		));
	}

	public function line($string, $style = null, $verbosity = null) {
		if ($this->option('loggable')) $string = date('[Y-m-d H:i:s] ') . ($style ? "{$style}: " : '') . $string;
		$styled = $style ? "<{$style}>{$string}</{$style}>" : $string;
		$this->output->writeln($styled, $this->parseVerbosity($verbosity));
	}
}
