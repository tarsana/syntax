<?php

use Tarsana\Syntax\ArraySyntax;
use Tarsana\Syntax\Factory as S;
use Tarsana\Syntax\NumberSyntax;
use Tarsana\Syntax\ObjectSyntax;
use Tarsana\Syntax\StringSyntax;
use Tarsana\Syntax\SyntaxSyntax;
use Tarsana\Syntax\BooleanSyntax;

class SyntaxSyntaxTest extends PHPUnit_Framework_TestCase {

    protected function checkParse($text, $class, $description, $isRequired)
    {
        $s = S::fromString($text);
        $this->assertTrue($s instanceof $class);
        $this->assertEquals($description, $s->description());
        $this->assertEquals($isRequired, $s->isRequired());
        return $s;
    }

    protected function checkDump($text, $syntax)
    {
        $this->assertEquals($text, S::syntax()->dump($syntax));
    }

    public function test_getters_and_setters()
    {
        $ss = (new SyntaxSyntax)
            ->arraySeparator('/')
            ->objectSeparator('*')
            ->fieldsSeparator(' ');

        $this->assertEquals('/', $ss->arraySeparator());
        $this->assertEquals('*', $ss->objectSeparator());
        $this->assertEquals(' ', $ss->fieldsSeparator());
    }

    public function test_error_case()
    {
        $this->assertFalse(S::syntax()->canParse('foo{'));
        $this->assertFalse(S::syntax()->canDump(new SyntaxSyntax));
    }

    //----------------------------------------- String ---------------------------------------------------------

    public function test_parse_string()
    {
        $this->checkParse('name', 'Tarsana\Syntax\StringSyntax', 'name', true);
        $this->checkParse('', 'Tarsana\Syntax\StringSyntax', '', true);
        $this->checkParse('[name]', 'Tarsana\Syntax\StringSyntax', 'name', false);
    }

    public function test_dump_string()
    {
        $this->checkDump('name',
            S::string()->description('name')
        );
        $this->checkDump('[name]',
            S::string('')->description('name')
        );
        $this->checkDump('',
            S::string()->description('')
        );
    }


    //----------------------------------------- Number ---------------------------------------------------------

    public function test_parse_number()
    {
        $this->checkParse('#my_number', 'Tarsana\Syntax\NumberSyntax', 'my_number', true);
        $this->checkParse('[#my_number]', 'Tarsana\Syntax\NumberSyntax', 'my_number', false);
    }

    public function test_dump_number()
    {
        $this->checkDump('#my_number',
            S::number()->description('my_number')
        );
        $this->checkDump('[#my_number]',
            S::number('')->description('my_number')
        );
    }


    //----------------------------------------- Boolean ---------------------------------------------------------

    public function test_parse_boolean()
    {
        $this->checkParse('is-valid?', 'Tarsana\Syntax\BooleanSyntax', 'is-valid', true);
        $this->checkParse('[is-valid?]', 'Tarsana\Syntax\BooleanSyntax', 'is-valid', false);
    }

    public function test_dump_boolean()
    {
        $this->checkDump('is-valid?',
            S::boolean()->description('is-valid')
        );
        $this->checkDump('[is-valid?]',
            S::boolean('')->description('is-valid')
        );
    }


    //----------------------------------------- Array ---------------------------------------------------------

    public function test_parse_simple_array()
    {
        // Array of strings with default separator
        $s = $this->checkParse('names[]', 'Tarsana\Syntax\ArraySyntax', 'names', true);
        $this->assertEquals(['foo', ' bar', 'baz'], $s->parse('foo, bar,baz'));

        // Array of strings with custom separator
        $s = $this->checkParse('names[|]', 'Tarsana\Syntax\ArraySyntax', 'names', true);
        $this->assertEquals(['foo', ' bar', 'baz'], $s->parse('foo| bar|baz'));

        // Array of numbers with custom separator
        $s = $this->checkParse('#numbers[|]', 'Tarsana\Syntax\ArraySyntax', 'numbers', true);
        $this->assertEquals([1, 3, 5], $s->parse('1|3|5'));

        // Array of booleans without description
        $s = $this->checkParse('?[:]', 'Tarsana\Syntax\ArraySyntax', '', true);
        $this->assertEquals([true, false, true], $s->parse('true:no:yes'));

        // Optional array of strings
        $s = $this->checkParse('[names[]]', 'Tarsana\Syntax\ArraySyntax', 'names', false);
        $this->assertEquals(['foo', ' bar', 'baz'], $s->parse('foo, bar,baz'));
        $this->assertEquals('', $s->parse(''));
    }

    public function test_dump_array()
    {
        $this->checkDump(
            'names[,]',
            S::arr(S::string(), ',')->description('names')
        );
        $this->checkDump(
            '#numbers[|]',
            S::arr(S::number(), '|')->description('numbers')
        );
        $this->checkDump(
            '?[:]',
            S::arr(S::boolean(), ':')->description('')
        );
        $this->checkDump(
            '[#names[|]]',
            S::arr(S::number(), '|', '')->description('names')
        );
    }

    public function test_parse_array_of_arrays()
    {
        $s = $this->checkParse('#matrix[,][ ]', 'Tarsana\Syntax\ArraySyntax', 'matrix', true);
        $this->assertEquals([[1, 2], [3, 4]], $s->parse('1,2 3,4'));
    }

    public function test_dump_array_of_arrays()
    {
        $this->checkDump(
            '#matrix[,][ ]',
            S::arr(S::arr(S::number(), ',')->description(''), ' ')->description('matrix')
        );
    }

    public function test_parse_array_of_objects()
    {
        $s = $this->checkParse('persons{name,#age}[]', 'Tarsana\Syntax\ArraySyntax', 'persons', true);
        $this->assertEquals([
            (object) ['name' => 'Foo', 'age' => 26],
            (object) ['name' => 'Bar', 'age' => 20]
        ], $s->parse('Foo:26,Bar:20'));
    }

    public function test_dump_array_of_objects()
    {
        $this->checkDump(
            'persons{ ,name,#age}[|]',
            S::arr(S::obj([
                'name' => S::string(),
                'age'  => S::number()
            ],' ')->description(''), '|')->description('persons')
        );
    }

    public function test_custom_default_array_separator()
    {
        $s = (new SyntaxSyntax)->arraySeparator('|')->parse('names[]');
        $this->assertTrue($s instanceof ArraySyntax);
        $this->assertEquals('names', $s->description());
        $this->assertTrue($s->isRequired());
        $this->assertEquals(['foo', 'bar', 'baz'], $s->parse('foo|bar|baz'));
    }


    //----------------------------------------- Object ---------------------------------------------------------

    public function test_parse_simple_object()
    {
        // Simple Object
        $s = $this->checkParse('person{:,name, #age, vip?,friends[]}', 'Tarsana\Syntax\ObjectSyntax', 'person', true);
        $this->assertEquals(
            (object) ['name' => 'Foo', 'age' => 12, 'vip' => true, 'friends' => ['Bar', 'Baz']],
            $s->parse('Foo:12:true:Bar,Baz')
        );
        // Simple optional object
        $s = $this->checkParse('[person{:,name,#age,friends[]}]', 'Tarsana\Syntax\ObjectSyntax', 'person', false);
        $this->assertEquals(
            (object) ['name' => 'Foo', 'age' => 12, 'friends' => ['Bar', 'Baz']],
            $s->parse('Foo:12:Bar,Baz')
        );
        $this->assertEquals('', $s->parse(''));
        // Empty Object
        $s = $this->checkParse('{}', 'Tarsana\Syntax\ObjectSyntax', '', true);
        $this->assertEquals((object) [], $s->parse(''));
    }

    public function test_dump_object()
    {
        $this->checkDump(
            'person{:,name,#age,vip?,friends[,]}',
            S::obj([
                'name'    => S::string(),
                'age'     => S::number(),
                'vip'     => S::boolean(),
                'friends' => S::arr()->description(',')
            ], ':')->description('person')
        );
        $this->checkDump(
            '[person{:,name,#age,friends[,]}]',
            S::obj([
                'name'    => S::string(),
                'age'     => S::number(),
                'friends' => S::arr()->description(',')
            ], ':', '')->description('person')
        );
        $this->checkDump(
            '{:,}',
            S::obj([], ':')
        );
    }

    public function test_parse_object_with_one_field()
    {
        // one required field
        $s = $this->checkParse('{name}', 'Tarsana\Syntax\ObjectSyntax', '', true);
        $this->assertEquals(
            (object) ['name' => 'me'],
            $s->parse('me')
        );
    }

    public function test_dump_object_with_one_field()
    {
        $this->checkDump(
            '{ ,name}',
            S::obj([
                'name'    => S::string(),
            ], ' ')->description('')
        );
    }

    public function test_parse_object_with_optional_fields()
    {
        // one field and it's optional
        // if all fields are optional, then the object is optional ?
        $s = $this->checkParse('{[name]}', 'Tarsana\Syntax\ObjectSyntax', '', true);
        $this->assertEquals(
            (object) ['name' => ''],
            $s->parse('')
        );
        // one optional field among three
        // using ' ' as default separator
        $s = $this->checkParse('person{name,[#age],friends[]}', 'Tarsana\Syntax\ObjectSyntax', 'person', true);
        $this->assertEquals(
            (object) ['name' => 'Foo', 'age' => '', 'friends' => ['Bar', 'Baz']],
            $s->parse('Foo:Bar,Baz')
        );
    }

    public function test_dump_object_with_optional_fields()
    {
        $this->checkDump(
            '{ ,[name]}',
            S::obj([
                'name'    => S::string(''),
            ], ' ')->description('')
        );
        $this->checkDump(
            'person{:,name,[#age],friends[,]}',
            S::obj([
                'name'    => S::string(),
                'age'     => S::number(''),
                'friends' => S::arr()->description(',')
            ], ':')->description('person')
        );
    }

    public function test_parse_object_containing_objects()
    {
        $s = $this->checkParse('user{name,accounts{ ,site,login,[pass]}[]}', 'Tarsana\Syntax\ObjectSyntax', 'user', true);
        $this->assertEquals((object) ['name' => 'Foo', 'accounts' => [
            (object) ['site' => 'fb', 'login' => 'foo', 'pass' => '***'],
            (object) ['site' => 'gh', 'login' => 'mefoo', 'pass' => '']
            ]],
            $s->parse('Foo:fb foo ***,gh mefoo')
        );
    }

    public function test_dump_object_containing_objects()
    {
        $this->checkDump(
            'user{ ,name,accounts{:,site,login,[pass]}[,]}',
            S::obj([
                'name'     => S::string(),
                'accounts' => S::arr(S::obj([
                    'site'  => S::string(),
                    'login' => S::string(),
                    'pass'  => S::string('')
                ], ':'), ',')
            ], ' ')->description('user')
        );
    }

    public function test_custom_default_object_separator()
    {
        $s = (new SyntaxSyntax)->objectSeparator(' ')->parse('person{name,#age}');
        $this->assertTrue($s instanceof ObjectSyntax);
        $this->assertEquals('person', $s->description());
        $this->assertTrue($s->isRequired());
        $this->assertEquals((object)['name' => 'bar', 'age' => 11], $s->parse('bar 11'));
    }

    public function test_custom_object_fields_separator()
    {
        $s = (new SyntaxSyntax)->fieldsSeparator(' ')->parse('person{name #age}');
        $this->assertTrue($s instanceof ObjectSyntax);
        $this->assertEquals('person', $s->description());
        $this->assertTrue($s->isRequired());
        $this->assertEquals((object)['name' => 'bar', 'age' => 11], $s->parse('bar:11'));
    }

}
