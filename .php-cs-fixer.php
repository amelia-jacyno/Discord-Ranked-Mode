<?php

$rules = [
	'@Symfony' => true,
	'concat_space' => [
		'spacing' => 'one'
	],
];
$finder = PhpCsFixer\Finder::create()
	->in(['bin', 'src', 'config', 'public']);

return (new PhpCsFixer\Config())
	->setRules($rules)
	->setFinder($finder);