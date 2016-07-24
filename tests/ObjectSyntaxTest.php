<?php

use Tarsana\Syntax\StringSyntax;
use Tarsana\Syntax\NumberSyntax;
use Tarsana\Syntax\Factory as S;

class ObjectSyntaxTest extends PHPUnit_Framework_TestCase {

    public function test_getters_and_setters()
    {
        $syntax = S::obj()
            ->separator('|')
            ->fields([
                'name' => S::string(),
                 'age' => S::number()
            ]);

        $fields = $syntax->fields();
        $this->assertEquals('|', $syntax->separator());
        $this->assertEquals(2, count($fields));
        $this->assertTrue($fields['name'] instanceof StringSyntax);
        $this->assertTrue( $fields['age'] instanceof NumberSyntax);
    }

    public function test_parse() {
        $syntax = S::obj([
            'name' => S::string(),
            'age' => S::number(),
            'is_programmer' => S::boolean(),
            'friends' => S::arr()
        ]);

        $object = (object) [
            'name' => 'Foo',
            'age' => 76,
            'is_programmer' => false,
            'friends' => ['Bar', 'Baz']
        ];

        $this->assertEquals($object, $syntax->parse('Foo:76:no:Bar,Baz'));
    }

    public function test_to_string() {
        $syntax = S::obj([
            'name' => S::string(),
            'age' => S::number(),
            'is_programmer' => S::boolean(),
            'friends' => S::arr()
        ]);
        $this->assertEquals("object {name: (string), age: (number), is_programmer: (boolean), friends: (array of (string) separated by ',')} separated by ':'", "{$syntax}");
    }

    public function test_parse_custom_separator() {
        $syntax = S::obj([
            'name' => S::string(),
            'age' => S::number(),
            'is_programmer' => S::boolean(),
            'friends' => S::arr()
        ], '|');

        $object = (object) [
            'name' => 'Foo',
            'age' => 76,
            'is_programmer' => false,
            'friends' => ['Bar', 'Baz']
        ];

        $this->assertEquals($object, $syntax->parse('Foo|76|false|Bar,Baz'));
    }

    public function test_parse_with_optional_fields() {
        $syntax = S::obj([
            'name' => S::string(),
            'is_programmer' => S::boolean(false),
            'age' => S::number(),
            'friends' => S::arr(S::string(), ',', [])
        ]);

        $object = (object) [
            'name' => 'Foo',
            'is_programmer' => false,
            'age' => 76,
            'friends' => ['Bar', 'Baz']
        ];
        $this->assertEquals($object, $syntax->parse('Foo:76:Bar,Baz'));

        $object = (object) [
            'name' => 'Foo',
            'is_programmer' => true,
            'age' => 76,
            'friends' => []
        ];
        $this->assertEquals($object, $syntax->parse('Foo:yes:76'));
    }

    public function test_parse_complex_object() {
        $test = $this->getComplexTestCase();

        $this->assertEquals($test['object'], $test['syntax']->parse($test['text']));
    }

    /**
     * @expectedException Tarsana\Syntax\Exceptions\ParseException
     */
    public function test_parse_missing_fields() {
        $syntax = S::obj([
            'name' => S::string(),
            'age' => S::number(),
            'is_programmer' => S::boolean(),
            'friends' => S::arr()
        ]);
        $syntax->parse('Foo:23:yes');
    }

    /**
     * @expectedException Tarsana\Syntax\Exceptions\ParseException
     */
    public function test_parse_missing_required_fields() {
        $syntax = S::obj([
            'name' => S::string(),
            'age' => S::number(23),
            'is_programmer' => S::boolean(false),
            'friends' => S::arr()
        ]);
        $syntax->parse('Foo:5:yes');
    }

    /**
     * @expectedException Tarsana\Syntax\Exceptions\ParseException
     */
    public function test_parse_too_much_items() {
        $syntax = S::obj([
            'name' => S::string(),
            'age' => S::number(),
            'is_programmer' => S::boolean(),
            'friends' => S::arr()
        ]);
        $syntax->parse('Foo:5:yes:Bar,Baz:additional');
    }

    /**
     * @expectedException Tarsana\Syntax\Exceptions\ParseException
     */
    public function test_parse_wrong_required_field() {
        $syntax = S::obj([
            'name' => S::string(),
            'age' => S::number(),
            'is_programmer' => S::boolean(),
            'friends' => S::arr()
        ]);
        $syntax->parse('Foo:weird:yes:Bar,Baz');
    }

    /**
     * @expectedException Tarsana\Syntax\Exceptions\ParseException
     */
    public function test_parse_missing_required_fields_at_the_end() {
        $syntax = S::obj([
            'name' => S::string(),
            'age' => S::number(23),
            'is_programmer' => S::boolean(false),
            'friends' => S::arr()
        ]);
        $syntax->parse('Foo:29:yes');
    }

    public function test_dump() {
        $syntax = S::obj([
            'name' => S::string(),
            'age' => S::number(),
            'is_programmer' => S::boolean(),
            'friends' => S::arr()
        ]);

        $object = (object) [
            'name' => 'Foo',
            'age' => 76,
            'is_programmer' => false,
            'friends' => ['Bar', 'Baz']
        ];

        $this->assertEquals('Foo:76:false:Bar,Baz', $syntax->dump($object));
    }

    public function test_dump_complex_object() {
        $test = $this->getComplexTestCase();

        $this->assertEquals($test['full_text'], $test['syntax']->dump($test['object']));
    }

    /**
     * @expectedException Tarsana\Syntax\Exceptions\DumpException
     */
    public function test_dump_missing_field() {
        $syntax = S::obj([
            'name' => S::string(),
            'age' => S::number(),
            'is_programmer' => S::boolean(),
            'friends' => S::arr()
        ]);

        $object = (object) [
            'name' => 'Foo',
            'age' => 76,
            'friends' => ['Bar', 'Baz']
        ];

        $syntax->dump($object);
    }

    /**
     * @expectedException Tarsana\Syntax\Exceptions\DumpException
     */
    public function test_dump_wrong_field() {
        $syntax = S::obj([
            'name' => S::string(),
            'age' => S::number(),
            'is_programmer' => S::boolean(),
            'friends' => S::arr()
        ]);

        $object = (object) [
            'name' => 'Foo',
            'age' => 'weird',
            'is_programmer' => false,
            'friends' => ['Bar', 'Baz']
        ];

        $this->assertFalse($syntax->canDump($object));
        $syntax->dump($object);
    }

    protected function getComplexTestCase() {

        $text = "Student Agent Smart,Stupid\n" .
                "name:string:public age:int year:int:protected,get:1 ".
                "count:int:static,protected,get:0\n" .
                'speak:void canLearn:bool:s|Subject|"math":protected';

        $fullText = "Student Agent Smart,Stupid\n" .
                "name:string:public: age:int:private,get,set: year:int:protected,get:1 ".
                "count:int:static,protected,get:0\n" .
                'speak:void::public canLearn:bool:s|Subject|"math":protected';

        $syntax = S::obj([
            'names' => S::obj([
                'class_name' => S::string(),
                'parents' => S::arr(S::string(), ',', []),
                'interfaces' => S::arr(S::string(), ',', [])
            ], ' '),
            'attrs' => S::arr(S::obj([
                'name' => S::string(),
                'type' => S::string(),
                'flags' => S::arr(S::string(), ',', ['private', 'get', 'set']),
                'default' => S::string('')
            ]), ' '),
            'methods' => S::arr(S::obj([
                'name' => S::string(),
                'return' => S::string(),
                'args' => S::arr(S::obj([
                    'name' => S::string(),
                    'type' => S::string(),
                    'default' => S::string('')
                ], '|'), ',', []),
                'flags' => S::arr(S::string(), ',', ['public'])
            ]), ' '),
        ], "\n");

        $object = (object) [
            'names' => (object) [
                'class_name' => 'Student',
                'parents' => ['Agent'],
                'interfaces' => ['Smart', 'Stupid']
            ],
            'attrs' => [
                (object) [
                    'name' => 'name',
                    'type' => 'string',
                    'flags' => ['public'],
                    'default' => ''
                ],
                (object) [
                    'name' => 'age',
                    'type' => 'int',
                    'flags' => ['private', 'get', 'set'],
                    'default' => ''
                ],
                (object) [
                    'name' => 'year',
                    'type' => 'int',
                    'flags' => ['protected', 'get'],
                    'default' => '1'
                ],
                (object) [
                    'name' => 'count',
                    'type' => 'int',
                    'flags' => ['static', 'protected', 'get'],
                    'default' => '0'
                ]
            ],
            'methods' => [
                (object) [
                    'name' => 'speak',
                    'return' => 'void',
                    'args' => [],
                    'flags' => ['public']
                ],
                (object) [
                    'name' => 'canLearn',
                    'return' => 'bool',
                    'args' => [
                        (object) [
                            'name' => 's',
                            'type' => 'Subject',
                            'default' => '"math"'
                        ]
                    ],
                    'flags' => ['protected']
                ]
            ]
        ];


        return [
            'syntax' => $syntax,
            'object' => $object,
            'text' => $text,
            'full_text' => $fullText
        ];
    }

}
