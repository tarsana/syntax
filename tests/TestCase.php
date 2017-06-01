<?php namespace Tarsana\Syntax\UnitTests;

use Tarsana\Syntax\ArraySyntax;
use Tarsana\Syntax\BooleanSyntax;
use Tarsana\Syntax\Exceptions\DumpException;
use Tarsana\Syntax\Exceptions\ParseException;
use Tarsana\Syntax\NumberSyntax;
use Tarsana\Syntax\ObjectSyntax;
use Tarsana\Syntax\OptionalSyntax;
use Tarsana\Syntax\StringSyntax;
use Tarsana\Syntax\Syntax;

class TestCase extends \PHPUnit\Framework\TestCase {

    public static function assertEquals($expected, $actual, $message = '', $delta = 0, $maxDepth = 10, $canonicalize = false, $ignoreCase = false) {
        if ($expected instanceof Syntax && $actual instanceof Syntax) {
            if (get_class($expected) != get_class($actual))
                throw new \Exception("'{$expected}' and '{$actual}' are not equal");
            if ($expected instanceof StringSyntax || $expected instanceof BooleanSyntax || $expected instanceof NumberSyntax)
                return;
            if ($expected instanceof OptionalSyntax)
                return $this->assertEquals($expected->syntax(), $actual->syntax())
                    && $this->assertEquals($expected->getDefault(), $actual->getDefault());
            if ($expected instanceof ArraySyntax)
                return $this->assertEquals($expected->syntax(), $actual->syntax())
                    && $this->assertEquals($expected->separator(), $actual->separator());
            if ($expected instanceof ObjectSyntax) {
                $this->assertEquals(
                    array_keys($expected->fields()),
                    array_keys($actual->fields())
                );
                foreach($expected->fields() as $name => $syntax) {
                    $this->assertEquals($syntax, $actual->field($name));
                }
                return $this->assertEquals($expected->separator(), $actual->separator());
            }
        }
        return parent::assertEquals($expected, $actual);
    }

    protected function assertParse(Syntax $syntax, array $tests, callable $equals = null) {
        if ($equals === null)
            $equals = [$this, 'assertEquals'];
        foreach ($tests as $test) {
            if (isset($test['result'])) {
                if (is_string($test['input']))
                    $test['input'] = [$test['input']];
                foreach ($test['input'] as $txt) {
                    $equals(
                        $test['result'],
                        $syntax->parse($txt)
                    );
                }
            } else if (isset($test['errors'])) {
                try {
                    $syntax->parse($test['input']);
                    throw new \Exception("No exception thrown for parsing '{$test['input']}' using syntax '{$syntax}'");
                } catch (ParseException $e) {
                    $errors = $test['errors'];
                    $size   = count($errors);
                    $index  = 0;
                    while ($index < $size) {
                        $this->assertEquals($errors[$index], "{$e->message()}");
                        $e = $e->previous();
                        $index ++;
                    }
                }
            }
        }
    }

    protected function assertDump(Syntax $syntax, array $tests) {
        foreach ($tests as $test) {
            if (isset($test['result'])) {
                $this->assertEquals(
                    $test['result'],
                    $syntax->dump($test['input'])
                );
            } else if (isset($test['errors'])) {
                try {
                    $syntax->dump($test['input']);
                    throw new \Exception("No exception thrown for dumping '{$test['input']}' using syntax '{$syntax}'");
                } catch (DumpException $e) {
                    $errors = $test['errors'];
                    $size   = count($errors);
                    $index  = 0;
                    while ($index < $size) {
                        $this->assertEquals($errors[$index], "{$e->message()}");
                        $e = $e->previous();
                        $index ++;
                    }
                }
            }
        }
    }

}
