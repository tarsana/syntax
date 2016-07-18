<?php

use Tarsana\Syntax\ArraySyntax;
use Tarsana\Syntax\BooleanSyntax;
use Tarsana\Syntax\NumberSyntax;
use Tarsana\Syntax\ObjectSyntax;
use Tarsana\Syntax\StringSyntax;
use Tarsana\Syntax\SyntaxSyntax;
use Tarsana\Syntax\Factory as S;

class SyntaxSyntaxTest extends PHPUnit_Framework_TestCase {

    protected static $ss;

    public static function setUpBeforeClass()
    {
        self::$ss = new SyntaxSyntax;
    }

    public static function tearDownAfterClass()
    {
        self::$ss = null;
    }

    protected function checkParse($text, $class, $description, $isRequired) {
        $s = self::$ss->parse($text);
        $this->assertTrue($s instanceof $class);
        $this->assertEquals($description, $s->description());
        $this->assertEquals($isRequired, $s->isRequired());
        return $s;
    }

    public function test_parse_string() {
        $this->checkParse('name', StringSyntax::class, 'name', true);
        $this->checkParse('', StringSyntax::class, '', true);
        $this->checkParse('[name]', StringSyntax::class, 'name', false);
    }

    public function test_parse_number() {
        $this->checkParse('#my_number', NumberSyntax::class, 'my_number', true);
        $this->checkParse('[#my_number]', NumberSyntax::class, 'my_number', false);
    }

    public function test_parse_boolean() {
        $this->checkParse('is-valid?', BooleanSyntax::class, 'is-valid', true);
        $this->checkParse('[is-valid?]', BooleanSyntax::class, 'is-valid', false);
    }

    public function test_parse_simple_array() {
        // Array of strings with default separator
        $s = $this->checkParse('names[]', ArraySyntax::class, 'names', true);
        $this->assertEquals(['foo', ' bar', 'baz'], $s->parse('foo, bar,baz'));

        // Array of strings with custom separator
        $s = $this->checkParse('names[|]', ArraySyntax::class, 'names', true);
        $this->assertEquals(['foo', ' bar', 'baz'], $s->parse('foo| bar|baz'));

        // Array of numbers with custom separator
        $s = $this->checkParse('#numbers[|]', ArraySyntax::class, 'numbers', true);
        $this->assertEquals([1, 3, 5], $s->parse('1|3|5'));

        // Array of booleans without description
        $s = $this->checkParse('?[:]', ArraySyntax::class, '', true);
        $this->assertEquals([true, false, true], $s->parse('true:no:yes'));

        // Optional array of strings
        $s = $this->checkParse('[names[]]', ArraySyntax::class, 'names', false);
        $this->assertEquals(['foo', ' bar', 'baz'], $s->parse('foo, bar,baz'));
        $this->assertEquals('', $s->parse(''));
    }

    public function test_parse_array_of_arrays() {
        $s = $this->checkParse('#matrix[,][ ]', ArraySyntax::class, 'matrix', true);
        $this->assertEquals([[1, 2], [3, 4]], $s->parse('1,2 3,4'));
    }

    public function test_parse_array_of_objects() {
        $s = $this->checkParse('persons{name,#age}[]', ArraySyntax::class, 'persons', true);
        $this->assertEquals([
            (object) ['name' => 'Foo', 'age' => 26],
            (object) ['name' => 'Bar', 'age' => 20]
        ], $s->parse('Foo 26,Bar 20'));
    }

    public function test_parse_simple_object() {
        // Simple Object
        $s = $this->checkParse('person{:,name, #age, vip?,friends[]}', ObjectSyntax::class, 'person', true);
        $this->assertEquals(
            (object) ['name' => 'Foo', 'age' => 12, 'vip' => true, 'friends' => ['Bar', 'Baz']],
            $s->parse('Foo:12:true:Bar,Baz')
        );
        // Simple optional object
        $s = $this->checkParse('[person{:,name,#age,friends[]}]', ObjectSyntax::class, 'person', false);
        $this->assertEquals(
            (object) ['name' => 'Foo', 'age' => 12, 'friends' => ['Bar', 'Baz']],
            $s->parse('Foo:12:Bar,Baz')
        );
        $this->assertEquals('', $s->parse(''));
    }

    public function test_parse_object_with_one_field() {
        // one required field
        $s = $this->checkParse('{name}', ObjectSyntax::class, '', true);
        $this->assertEquals(
            (object) ['name' => 'me'],
            $s->parse('me')
        );
    }

    public function test_parse_object_with_optional_fields() {
        // one field and it's optional
        // if all fields are optional, then the object is optional ?
        $s = $this->checkParse('{[name]}', ObjectSyntax::class, '', true);
        $this->assertEquals(
            (object) ['name' => ''],
            $s->parse('')
        );
        // one optional field among three
        // using ' ' as default separator
        $s = $this->checkParse('person{name,[#age],friends[]}', ObjectSyntax::class, 'person', true);
        $this->assertEquals(
            (object) ['name' => 'Foo', 'age' => '', 'friends' => ['Bar', 'Baz']],
            $s->parse('Foo Bar,Baz')
        );
    }

    public function test_parse_object_containing_objects() {
        $s = $this->checkParse('user{name,accounts{:,site,login,[pass]}[]}', ObjectSyntax::class, 'user', true);
        $this->assertEquals((object) ['name' => 'Foo', 'accounts' => [
            (object) ['site' => 'fb', 'login' => 'foo', 'pass' => '***'],
            (object) ['site' => 'gh', 'login' => 'mefoo', 'pass' => '']
            ]],
            $s->parse('Foo fb:foo:***,gh:mefoo')
        );
    }

    public function test_dump() {
        $ss = new SyntaxSyntax;

        $this->assertEquals('name', $ss->dump(S::string()->description('name')));
        $this->assertEquals('[name]', $ss->dump(S::string('')->description('name')));
        $this->assertEquals('', $ss->dump(S::string()->description('')));

        $this->assertEquals('is-valid?', $ss->dump(S::boolean()->description('is-valid')));
        $this->assertEquals('[is-valid?]', $ss->dump(S::boolean('')->description('is-valid')));

        $this->assertEquals('#number_of_numbers',
            $ss->dump(S::number()->description('number_of_numbers')
        ));
        $this->assertEquals('[#number_of_numbers]',
            $ss->dump(S::number('')->description('number_of_numbers')
        ));

        $this->assertEquals(
            'names[,]',
            $ss->dump(S::arr(S::string(), ',')->description('names'))
        );
        $this->assertEquals(
            '#names[|]',
            $ss->dump(S::arr(S::number(), '|')->description('names'))
        );
        $this->assertEquals(
            '[#names[|]]',
            $ss->dump(S::arr(S::number(), '|', '')->description('names'))
        );

        $this->assertEquals(
            'person{:,name,#age,friends[,]}',
            $ss->dump(S::obj([
                'name' => S::string(),
                'age' => S::number(),
                'friends' => S::arr(S::string())
            ], ':')->description('person'))
        );
        $this->assertEquals(
            '[person{:,name,#age,friends[,]}]',
            $ss->dump(S::obj([
                'name' => S::string(),
                'age' => S::number(),
                'friends' => S::arr(S::string())
            ], ':', '')->description('person'))
        );
    }
}
