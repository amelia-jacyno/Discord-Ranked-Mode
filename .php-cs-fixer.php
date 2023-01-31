<?php

$rules = [
	'@Symfony' => true,
	'concat_space' => [
		'spacing' => 'one'
	],
];
$finder = PhpCsFixer\Finder::create()
	->in(['bin', 'cli', 'src', 'public']);

return (new PhpCsFixer\Config())
	->setRules($rules)
	->setFinder($finder);