<?php namespace Tarsana\Syntax\UnitTests;

use Tarsana\Syntax\ArraySyntax;
use Tarsana\Syntax\BooleanSyntax;
use Tarsana\Syntax\Factory as S;
use Tarsana\Syntax\NumberSyntax;
use Tarsana\Syntax\ObjectSyntax;
use Tarsana\Syntax\OptionalSyntax;
use Tarsana\Syntax\StringSyntax;
use Tarsana\Syntax\Syntax;
use Tarsana\Syntax\SyntaxSyntax;

class SyntaxSyntaxTest extends TestCase {

    protected function parse(string $text, String $syntax) {
        $this->assertEquals($syntax, S::syntax()->parse($text));
    }

    protected function dump(Syntax $syntax, string $text) {
        $this->assertEquals($text, S::syntax()->dump($syntax));
    }

    public function test_string() {
        $this->parse('', S::string());
        $this->parse('string', S::string());
        $this->parse(' string ', S::string());
        $this->dump(S::string(), 'string');
    }

    public function test_optional_string() {
        $syntax =S::optional(S::string(), 'Yo');
        $this->parse('(string:Yo)', $syntax);
        $this->dump($syntax, '(string:"Yo")');
    }

    public function test_boolean() {
        $this->parse('boolean', S::boolean());
        $this->dump(S::boolean(), 'boolean');
    }

    public function test_optional_boolean() {
        $syntax =S::optional(S::boolean(), false);
        $this->parse('(boolean:false)', $syntax);
        $this->dump($syntax, '(boolean:false)');
    }

    public function test_number() {
        $this->parse('number', S::number());
        $this->dump(S::number(), 'number');
    }

    public function test_optional_number() {
        $syntax =S::optional(S::number(), false);
        $this->parse('(number:false)', $syntax);
        $this->dump($syntax, '(number:false)');
    }

    public function test_syntax() {
        $this->parse('syntax', S::syntax());
        $this->dump(S::syntax(), 'syntax');
    }

    public function test_array() {
        $syntax = S::array();
        $this->parse('[]', $syntax);
        $this->parse('[string]', $syntax);
        $this->dump($syntax, '[string|,]');
    }

    public function test_array_of_numbers() {
        $syntax = S::array(S::number());
        $this->parse('[number|,]', $syntax);
        $this->dump($syntax, '[number|,]');
    }

    public function test_optional_array_of_numbers() {
        $syntax = S::optional(S::array(S::number()), [1, 2, 3]);
        $this->parse('([number|,]:[1, 2, 3])', $syntax);
        $this->dump($syntax, '([number|,]:[1,2,3])');
    }

    public function test_array_with_custom_separator() {
        $syntax = S::array(S::number())->separator('/');
        $this->parse('[number|/]', $syntax);
        $this->dump($syntax, '[number|/]');
    }

    public function test_simple_object() {
        $syntax = S::object([
            'name' => S::string(),
            'age'  => S::number(),
            'vip'  => S::boolean(),
            'pets' => S::array()
        ]);
        $text = '{
            name: string,age: number,
            vip: boolean,
            pets: []
        }';
        $shortText = '{"name:string,age:number,vip:boolean,"pets:[string|,]""|:}';
        $this->parse($text, $syntax);
        $this->dump($syntax, $shortText);
    }
}
