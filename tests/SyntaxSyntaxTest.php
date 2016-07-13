<?php

use Tarsana\Syntax\ArraySyntax;
use Tarsana\Syntax\BooleanSyntax;
use Tarsana\Syntax\NumberSyntax;
use Tarsana\Syntax\ObjectSyntax;
use Tarsana\Syntax\StringSyntax;
use Tarsana\Syntax\SyntaxSyntax;
use Tarsana\Syntax\Factory as S;

class SyntaxSyntaxTest extends PHPUnit_Framework_TestCase {
    
    public function test_parse() {
        $ss = new SyntaxSyntax;

        $s = $ss->parse('name');
        $this->assertTrue($s instanceof StringSyntax);
        $this->assertEquals('name', $s->description());

        $s = $ss->parse('');
        $this->assertTrue($s instanceof StringSyntax);
        $this->assertEquals('', $s->description());

        $s = $ss->parse('#number_of_numbers');
        $this->assertTrue($s instanceof NumberSyntax);
        $this->assertEquals('number_of_numbers', $s->description());

        $s = $ss->parse('is-valid?');
        $this->assertEquals('is-valid', $s->description());
        $this->assertTrue($s instanceof BooleanSyntax);

        $s = $ss->parse('names[]');
        $this->assertEquals('names', $s->description());
        $this->assertTrue($s instanceof ArraySyntax);
        $this->assertEquals(['foo', ' bar', 'baz'], $s->parse('foo, bar,baz'));

        $s = $ss->parse('names[|]');
        $this->assertEquals('names', $s->description());
        $this->assertTrue($s instanceof ArraySyntax);
        $this->assertEquals(['foo', ' bar', 'baz'], $s->parse('foo| bar|baz'));

        $s = $ss->parse('#names[|]');
        $this->assertEquals('names', $s->description());
        $this->assertTrue($s instanceof ArraySyntax);
        $this->assertEquals([1, 2, 3], $s->parse('1| 2|3'));

        $s = $ss->parse('person{:,name,#age,friends[]}');
        $this->assertEquals('person', $s->description());
        $this->assertTrue($s instanceof ObjectSyntax);
        $this->assertEquals(
            (object) ['name' => 'Foo', 'age' => 12, 'friends' => ['Bar', 'Baz']], 
            $s->parse('Foo:12:Bar,Baz')
        );

    }

    public function test_dump() {
        $ss = new SyntaxSyntax;

        $this->assertEquals('name', $ss->dump(S::string()->description('name')));
        $this->assertEquals('', $ss->dump(S::string()->description('')));
        $this->assertEquals('is-valid?', $ss->dump(S::boolean()->description('is-valid')));
        $this->assertEquals('#number_of_numbers', 
            $ss->dump(S::number()->description('number_of_numbers')
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
            'person{:,name,#age,friends[,]}', 
            $ss->dump(S::obj([
                'name' => S::string(),
                'age' => S::number(),
                'friends' => S::arr(S::string())
            ], ':')->description('person'))
        );
    }
}
