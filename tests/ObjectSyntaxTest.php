<?php

namespace Tarsana\Syntax\UnitTests;

use Tarsana\Syntax\ArraySyntax;
use Tarsana\Syntax\BooleanSyntax;
use Tarsana\Syntax\Factory as S;
use Tarsana\Syntax\NumberSyntax;
use Tarsana\Syntax\ObjectSyntax;
use Tarsana\Syntax\StringSyntax;

class ObjectSyntaxTest extends TestCase
{
    public function test_getters_and_setters()
    {
        $syntax = new ObjectSyntax(['name' => S::array()], '|')
            ->fields(['name' => S::string()])
            ->field('age', S::number());

        $fields = $syntax->fields();
        $this->assertEqualsCompat('|', $syntax->separator());
        $this->assertEqualsCompat(2, count($fields));
        $this->assertTrue($syntax->field('name') instanceof StringSyntax);
        $this->assertTrue($syntax->field('age') instanceof NumberSyntax);
    }

    public function test_get_nested_fields()
    {
        $syntax = S::object(
            [
            'name' => S::string(),
            'repos' => S::array(
                S::object(
                    [
                    'name' => S::string(),
                    'stars' => S::number(),
                    ]
                )
            ),
            'profile' => S::object(
                [
                'active' => S::boolean(),
                'balance' => S::number()
                ]
            )
            ]
        );

        $this->assertTrue($syntax->field('repos') instanceof ArraySyntax);
        $this->assertTrue($syntax->field('repos.stars') instanceof NumberSyntax);
        $this->assertTrue($syntax->field('profile.active') instanceof BooleanSyntax);
    }

    public function test_construct_without_fields(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new ObjectSyntax([]);
    }

    public function test_get_unknown_field()
    {
        $this->expectException(\InvalidArgumentException::class);

        $syntax = S::object(['name' => S::string()])
            ->separator('|')
            ->field('age', S::number());

        $syntax->field('account');
    }

    public function test_to_string()
    {
        $syntax = S::object(
            [
            'name' => S::string(),
            'age' => S::number(),
            'is_programmer' => S::boolean(),
            'friends' => S::array()
            ]
        );
        $this->assertEqualsCompat("Object {name: String, age: Number, is_programmer: Boolean, friends: Array of (String) separated by ','} separated by ':'", "{$syntax}");
    }

    public function test_parse_all_fields_are_required()
    {
        // Parsing all required fields
        $syntax = S::object(
            [
            'name' => S::string(),
            'age' => S::number(),
            'is_programmer' => S::boolean(),
            'friends' => S::array()
            ]
        );

        $this->assertParse(
            $syntax,
            [[
            'input'  => 'Foo:76:no:Bar,Baz',
            'result' => (object) [
                'name' => 'Foo',
                'age' => 76,
                'is_programmer' => false,
                'friends' => ['Bar', 'Baz']
            ]
            ], [
            'input'  => '"Foo:Bar:Baz":76:no:"Bar,Baz",lorem',
            'result' => (object) [
                'name' => 'Foo:Bar:Baz',
                'age' => 76,
                'is_programmer' => false,
                'friends' => ['Bar,Baz', 'lorem']
            ]
            ], [
            'input'  => 'Foo:76::Bar,Baz',
            'errors' => [
                "Error while parsing 'Foo:76::Bar,Baz' as Object {name: String, age: Number, is_programmer: Boolean, friends: Array of (String) separated by ','} separated by ':' at character 7: Unable to parse the item '' for field 'is_programmer'",
                "Error while parsing '' as Boolean at character 0: " . BooleanSyntax::PARSE_ERROR
            ]
            ], [
            'input'  => 'Foo:76:false:Bar,Baz:additional',
            'errors' => [
                "Error while parsing 'Foo:76:false:Bar,Baz:additional' as Object {name: String, age: Number, is_programmer: Boolean, friends: Array of (String) separated by ','} separated by ':' at character 20: Additional items with no corresponding fields"
            ]
            ]]
        );

        // Changing the separator
        $syntax->separator('||');
        $this->assertParse(
            $syntax,
            [[
            'input'  => 'Foo||76||no||Bar,Baz',
            'result' => (object) [
                'name' => 'Foo',
                'age' => 76,
                'is_programmer' => false,
                'friends' => ['Bar', 'Baz']
            ]
            ], [
            'input'  => 'Foo||76||||Bar,Baz',
            'errors' => [
                "Error while parsing 'Foo||76||||Bar,Baz' as Object {name: String, age: Number, is_programmer: Boolean, friends: Array of (String) separated by ','} separated by '||' at character 9: Unable to parse the item '' for field 'is_programmer'",
                "Error while parsing '' as Boolean at character 0: " . BooleanSyntax::PARSE_ERROR
            ]
            ]]
        );
    }

    public function test_parse_all_fields_are_optional()
    {
        // Optional Fields
        $syntax = S::object(
            [
            'name' => S::optional(S::string(), 'Unknown'),
            'age' => S::optional(S::number(), 21),
            'is_programmer' => S::optional(S::boolean(), false),
            'friends' => S::optional(S::array(), [])
            ]
        );

        $this->assertParse(
            $syntax,
            [[
            'input'  => 'Foo:76:no:Bar,Baz',
            'result' => (object) [
                'name' => 'Foo',
                'age' => 76,
                'is_programmer' => false,
                'friends' => ['Bar', 'Baz']
            ]
            ], [
            'input'  => '',
            'result' => (object) [
                'name' => 'Unknown',
                'age' => 21,
                'is_programmer' => false,
                'friends' => []
            ]
            ], [
            'input'  => 'Me:yes',
            'result' => (object) [
                'name' => 'Me',
                'age' => 21,
                'is_programmer' => true,
                'friends' => []
            ]
            ], [
            'input'  => '27:23:yes',
            'result' => (object) [
                'name' => '27',
                'age' => 23,
                'is_programmer' => true,
                'friends' => []
            ]
            ], [
            'input'  => 'Me:code',
            'result' => (object) [
                'name' => 'Me',
                'age' => 21,
                'is_programmer' => false,
                'friends' => ['code']
            ]
            ]]
        );
    }

    public function test_parse_with_optional_fields()
    {
        $syntax = S::object(
            [
            'name' => S::string(),
            'is_programmer' => S::optional(S::boolean(), false),
            'age' => S::number(),
            'friends' => S::optional(S::array(), [])
            ]
        );

        $this->assertParse(
            $syntax,
            [[
            'input'  => 'Foo:76:Bar,Baz',
            'result' => (object) [
                'name' => 'Foo',
                'is_programmer' => false,
                'age' => 76,
                'friends' => ['Bar', 'Baz']
            ]
            ], [
            'input'  => 'Foo:yes:76',
            'result' => (object) [
                'name' => 'Foo',
                'is_programmer' => true,
                'age' => 76,
                'friends' => []
            ]
            ], [
            'input'  => 'Foo:yes:Bar,Baz',
            'errors' => [
                "Error while parsing 'Foo:yes:Bar,Baz' as Object {name: String, is_programmer: Optional Boolean, age: Number, friends: Optional Array of (String) separated by ','} separated by ':' at character 8: Unable to parse the item 'Bar,Baz' for field 'age'",
                "Error while parsing 'Bar,Baz' as Number at character 0: " . NumberSyntax::ERROR
            ]
            ], [
            'input'  => 'Foo:yes',
            'errors' => [
                "Error while parsing 'Foo:yes' as Object {name: String, is_programmer: Optional Boolean, age: Number, friends: Optional Array of (String) separated by ','} separated by ':' at character 8: No item left for field 'age'"
            ]
            ]]
        );
    }

    public function test_dump()
    {
        $syntax = S::object(
            [
            'name' => S::string(),
            'age' => S::number(),
            'is_programmer' => S::boolean(),
            'friends' => S::array()
            ]
        );

        $this->assertDump(
            $syntax,
            [[
            'input'  => (object) [
                'name' => 'Foo',
                'age'  => 76,
                'is_programmer' => false,
                'friends' => ['Bar', 'Baz']
            ],
            'result' => 'Foo:76:false:Bar,Baz'
            ], [
            'input'  => (object) [
                'name' => 'Foo',
                'age'  => 76,
                'friends' => ['Bar', 'Baz']
            ],
            'errors' => [
                "Error while dumping some input as Object {name: String, age: Number, is_programmer: Boolean, friends: Array of (String) separated by ','} separated by ':': Missing field 'is_programmer'"
            ]
            ], [
            'input'  => (object) [
                'name' => 'Foo',
                'age'  => 'Yo',
                'friends' => ['Bar', 'Baz']
            ],
            'errors' => [
                "Error while dumping some input as Object {name: String, age: Number, is_programmer: Boolean, friends: Array of (String) separated by ','} separated by ':': Unable to dump the field 'age'",
                "Error while dumping some input as Number: " . NumberSyntax::ERROR
            ]
            ]]
        );
    }
}
