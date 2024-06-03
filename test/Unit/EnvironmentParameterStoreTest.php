<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit;

use Cspray\AnnotatedContainer\ContainerFactory\EnvironmentParameterStore;
use Cspray\AnnotatedContainer\Exception\EnvironmentVarNotFound;
use PHPUnit\Framework\TestCase;
use function Cspray\Typiphy\stringType;

class EnvironmentParameterStoreTest extends TestCase {

    public function testGetEnvironmentParameterStoreName() {
        $this->assertSame('env', (new EnvironmentParameterStore())->getName());
    }

    public function testGetEnvironmentVariableExists() {
        putenv('ANNOTATED_CONTAINER_TEST_ENV=foobar');
        $this->assertSame('foobar', (new EnvironmentParameterStore())->fetch(stringType(), 'ANNOTATED_CONTAINER_TEST_ENV'));
    }

    public function testGetEnvironmentVariableDoesNotExistThrowsException() {
        $this->expectException(EnvironmentVarNotFound::class);
        $this->expectExceptionMessage('The key "ANNOTATED_CONTAINER_NOT_PRESENT" is not available in store "env".');
        (new EnvironmentParameterStore())->fetch(stringType(), 'ANNOTATED_CONTAINER_NOT_PRESENT');
    }
}
