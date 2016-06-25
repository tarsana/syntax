<?php

use Tarsana\Syntax\Factory as S;

class ObjectSyntaxTest extends PHPUnit_Framework_TestCase {
    
    public function testParse() {
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

        $this->assertEquals($object, $syntax->parse('Foo:76:0:Bar,Baz'));
    }

    public function testParseCustomSeparator() {
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

        $this->assertEquals($object, $syntax->parse('Foo|76|0|Bar,Baz'));
    }

    public function testParseWithOptionalFields() {
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

    public function testParseComplexObject() {
        $test = $this->getComplexTestCase();

        $this->assertEquals($test['object'], $test['syntax']->parse($test['text']));
    }

    /**
     * @expectedException Tarsana\Syntax\Exceptions\ParseException
     */
    public function testParseMissingFields() {
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
    public function testParseMissingRequiredFields() {
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
    public function testParseTooMuchItems() {
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
    public function testParseWrongRequiredField() {
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
    public function testParseMissingRequiredFieldsAtTheEnd() {
        $syntax = S::obj([
            'name' => S::string(),
            'age' => S::number(23),
            'is_programmer' => S::boolean(false),
            'friends' => S::arr()
        ]);
        $syntax->parse('Foo:29:yes');
    }

    public function testDump() {
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

    public function testDumpComplexObject() {
        $test = $this->getComplexTestCase();

        $this->assertEquals($test['full_text'], $test['syntax']->dump($test['object']));
    }

    /**
     * @expectedException Tarsana\Syntax\Exceptions\DumpException
     */
    public function testDumpMissingField() {
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
    public function testDumpWrongField() {
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
