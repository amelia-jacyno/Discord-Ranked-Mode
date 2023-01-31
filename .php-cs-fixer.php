<?php

$rules = [
	'@Symfony' => true,
];
$finder = PhpCsFixer\Finder::create()
	->in(['bin', 'cli', 'src', 'public']);

return (new PhpCsFixer\Config())
	->setRules($rules)
	->setFinder($finder);