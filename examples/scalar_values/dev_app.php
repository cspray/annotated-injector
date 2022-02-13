<?php declare(strict_types=1);

require dirname(__DIR__, 2) . '/vendor/autoload.php';

$compiler = new \Cspray\AnnotatedContainer\PhpParserInjectorDefinitionCompiler();
$injectorDefinition = $compiler->compileDirectory('dev', __DIR__ . '/src');
$injector = (new Cspray\AnnotatedContainer\AurynInjectorFactory)->createContainer($injectorDefinition);

$scalarGetter = $injector->make(\Acme\AnnotatedContainerDemo\ScalarGetter::class);

var_dump($scalarGetter);
