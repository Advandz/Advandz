<?php

class InputTest extends PHPUnit_Framework_TestCase
{
    private $Input;

    public function setUp()
    {
        $this->Input = new Advandz\Component\Input();
    }

    /**
     * @covers Input::isEmail
     */
    public function testIsEmail()
    {
        $this->assertTrue($this->Input->isEmail('someone@somedomain.com', false));
        $this->assertFalse($this->Input->isEmail('', false));
        $this->assertFalse($this->Input->isEmail('a@b', false));
        $this->assertTrue($this->Input->isEmail('someone@google.com'));
        $this->assertFalse($this->Input->isEmail('someone@mnbvcxzljhgfdsapoiuytrewq.tld', true));
    }

    /**
     * @covers Input::isEmpty
     */
    public function testIsEmpty()
    {
        $this->assertTrue($this->Input->isEmpty(null));
        $this->assertTrue($this->Input->isEmpty(''));
        $this->assertFalse($this->Input->isEmpty('hello world'));
        $this->assertFalse($this->Input->isEmpty(0));
    }

    /**
     * @covers Input::isPassword
     * @dataProvider isPasswordProvider
     */
    public function testIsPassword($str, $length, $type, $regex, $result)
    {
        $this->assertSame($result, $this->Input->isPassword($str, $length, $type, $regex));
    }

    /**
     * Data provider for testIsPassword.
     */
    public function isPasswordProvider()
    {
        return [
            // any
            ['password', 8, 'any', null, true],
            ['', 8, 'any', null, false],
            ['pass', 4, 'any', null, true],
            ['pass', 6, 'any', null, false],
            // alpha_num
            ['password123', 8, 'alpha_num', null, true],
            ['password123_', 8, 'alpha_num', null, false],
            // alpha
            ['password', 8, 'alpha', null, true],
            ['password1', 8, 'alpha', null, false],
            // any_no_space
            ['password_123', 8, 'any_no_space', null, true],
            ['password 123', 8, 'any_no_space', null, false],
            // num
            ['12345678', 8, 'num', null, true],
            ['1234567.8', 8, 'num', null, false],
            // custom
            ['123-4567', 0, 'custom', '/[0-9]{3}-[0-9]{4}/i', true],
            ['1234-567', 0, 'custom', '/[0-9]{3}-[0-9]{4}/i', false],
        ];
    }

    /**
     * @covers Input::isDate()
     * @dataProvider isDateProvider
     */
    public function testIsDate($date, $min, $max, $result)
    {
        $this->assertSame($result, $this->Input->isDate($date, $min, $max));
    }

    /**
     * Data provider for testIsDate.
     */
    public function isDateProvider()
    {
        return [
            [time(), null, null, true],
            [null, null, null, false],
            ['2011-01-01', null, null, true],
            ['non-date-string', null, null, false],
            ['2011-01-01T00:00:00Z', '2011-01-01T00:00:00-07:00', null, false],
            ['2011-01-01T00:00:00Z', '2011-01-01T00:00:00-00:00', null, true],
            ['2011-01-31T00:00:00Z', null, '2011-01-31T00:00:00-00:00', true],
            ['2011-01-31T00:00:00Z', null, '2011-02-01T00:00:00-00:00', true],
            ['2111-01-31T00:00:00Z', null, '2011-02-01T00:00:00-00:00', false],
        ];
    }

    /**
     * @covers Input::matches
     */
    public function testMatches()
    {
        $this->assertTrue($this->Input->matches('abc', '/^[a][b][c]$/'));
        $this->assertFalse($this->Input->matches('ABC', '/^[a][b][c]$/'));
    }

    /**
     * @covers Input::compares
     * @dataProvider comparesProvider
     */
    public function testCompares($a, $op, $b, $result)
    {
        $this->assertSame($result, $this->Input->compares($a, $op, $b));
    }

    /**
     * @expectedException Exception
     */
    public function testComparesException()
    {
        $this->Input->compares(1, '&', 0);
    }

    /**
     * Data provider for testCompares.
     */
    public function comparesProvider()
    {
        return [
            // >
            [1, '>', 0, true],
            [1, '>', 2, false],
            // <
            [1, '<', 2, true],
            [1, '<', 0, false],
            // >=
            [1, '>=', 1, true],
            [1, '>=', 2, false],
            // <=
            [1, '<=', 1, true],
            [1, '<=', 0, false],
            // ==
            [1, '==', '1', true],
            [1, '==', 2, false],
            // ===
            [1, '===', 1, true],
            [1, '===', '1', false],
            // !=
            [1, '!=', 2, true],
            [1, '!=', 1, false],
            // !===
            [1, '!==', 2, true],
            [1, '!==', 1, false],
        ];
    }

    /**
     * @covers Input::between
     */
    public function testBetween()
    {
        $this->assertTrue($this->Input->between(3, 1, 3, true));
        $this->assertFalse($this->Input->between(3, 1, 3, false));
    }

    /**
     * @covers Input::minLength
     */
    public function testMinLength()
    {
        $this->assertTrue($this->Input->minLength('hello', 5));
        $this->assertFalse($this->Input->minLength('hello world', 12));
    }

    /**
     * @covers Input::maxLength
     */
    public function testMaxLength()
    {
        $this->assertTrue($this->Input->maxLength('hello', 5));
        $this->assertFalse($this->Input->maxLength('hello world', 5));
    }

    /**
     * @covers Input::betweenLength
     */
    public function testBetweenLength()
    {
        $this->assertTrue($this->Input->betweenLength('hello', 5, 11));
        $this->assertTrue($this->Input->betweenLength('hello world', 5, 11));
        $this->assertFalse($this->Input->betweenLength('hello world!', 5, 11));
    }

    /**
     * @covers Input::setErrors
     * @covers Input::errors
     */
    public function testSetErrors()
    {
        $errors = [
            'key' => [
                'type' => 'Error Message',
            ],
        ];
        $this->Input->setErrors($errors);
        $this->assertSame($errors, $this->Input->errors());
    }

    /**
     * @covers Input::setRules
     * @covers Input::validates
     * @covers Input::pathSet
     * @covers Input::clearLeaves
     * @covers Input::array_walk_recursive
     * @covers Input::validateRule
     * @covers Input::formatData
     * @covers Input::replaceLinkedParams
     * @covers Input::processValidation
     * @dataProvider inputPreFormatProvider
     */
    public function testPreFormat($rules, $data, $formatted_data)
    {
        // Set the rules to test
        $this->Input->setRules($rules);

        // Attempt to validate $data against $rules
        $this->Input->validates($data);

        // Ensure that data is now modified such that is matches our expected $formatted_data
        $this->assertSame($formatted_data, $data);
    }

    /**
     * @covers Input::setRules
     * @covers Input::validates
     * @covers Input::pathSet
     * @covers Input::clearLeaves
     * @covers Input::array_walk_recursive
     * @covers Input::validateRule
     * @covers Input::formatData
     * @covers Input::replaceLinkedParams
     * @covers Input::processValidation
     * @dataProvider inputPostFormatProvider
     */
    public function testPostFormat($rules, $data, $formatted_data)
    {
        // Set the rules to test
        $this->Input->setRules($rules);

        // Attempt to validate $data against $rules
        $this->Input->validates($data);

        // Ensure that data is now modified such that is matches our expected $formatted_data
        $this->assertSame($formatted_data, $data);
    }

    /**
     * @covers Input::setRules
     * @covers Input::validates
     * @covers Input::pathSet
     * @covers Input::clearLeaves
     * @covers Input::array_walk_recursive
     * @covers Input::validateRule
     * @covers Input::formatData
     * @covers Input::replaceLinkedParams
     * @covers Input::processValidation
     * @dataProvider inputValidationProvider
     */
    public function testValidation($rules, $data, $result)
    {
        // Set the rules to test
        $this->Input->setRules($rules);

        // Attempt to validate $data against $rules
        $this->assertSame($result, $this->Input->validates($data));
    }

    /**
     * @covers Input::setRules
     * @covers Input::validates
     * @covers Input::replaceLinkedParams
     */
    public function testValidationLinkedParams()
    {
        $rules = [
            'items[][name]' => [
                'valid' => [
                    'rule' => [
                        function ($name, $price) {
                            return $name !== null && is_numeric($price);
                        },
                        ['_linked' => 'items[][price]'],
                    ],
                ],
            ],
        ];
        $data = [
            'items' => [
                ['name' => 'Item 1', 'price' => 1.50],
                ['name' => 'Item 2', 'price' => 2.75],
            ],
        ];

        $this->Input->setRules($rules);
        $this->assertTrue($this->Input->validates($data));

        unset($data['items'][1]['price']);
        $this->assertFalse($this->Input->validates($data));
    }

    public function inputPreFormatProvider()
    {
        return $this->getInputDataFormatting('pre_format');
    }

    public function inputPostFormatProvider()
    {
        return $this->getInputDataFormatting('post_format');
    }

    protected function getInputDataFormatting($action)
    {
        $rule_sets = [
            [
                'name' => [
                    'format' => [
                        'rule'   => 'isEmpty',
                        'negate' => true,
                        $action  => 'strtolower',
                    ],
                ],
                'company' => [
                    'format' => [
                        'rule'   => 'isEmpty',
                        'negate' => true,
                        $action  => 'strtoupper',
                    ],
                ],
            ],
            [
                'name[]' => [
                    'format' => [
                        'rule'   => 'isEmpty',
                        'negate' => true,
                        $action  => 'strtolower',
                    ],
                ],
                'company[]' => [
                    'format' => [
                        'rule'   => 'isEmpty',
                        'negate' => true,
                        $action  => ['strtoupper'],
                    ],
                ],
            ],
        ];

        $data_sets = [
            [
                'name'    => 'Person Name',
                'company' => 'Company Name',
            ],
            [
                'name' => [
                    'Person Name 1',
                    'Person Name 2',
                ],
                'company' => [
                    'Company Name 1',
                    'Company Name 2',
                ],
            ],
        ];

        $formatted_data = $data_sets;

        $formatted_data[0]['name']    = strtolower($formatted_data[0]['name']);
        $formatted_data[0]['company'] = strtoupper($formatted_data[0]['company']);
        foreach ($formatted_data[1]['name'] as &$result) {
            $result = strtolower($result);
        }
        foreach ($formatted_data[1]['company'] as &$result) {
            $result = strtoupper($result);
        }

        $data = [];
        foreach ($rule_sets as $i => $value) {
            $data[] = [$rule_sets[$i], $data_sets[$i], $formatted_data[$i]];
        }

        return $data;
    }

    public function inputValidationProvider()
    {
        $rule_sets = [
            // scalar
            [
                'name' => [
                    'format' => [
                        'rule'    => 'isEmpty',
                        'negate'  => true,
                        'message' => 'name can not be empty',
                    ],
                ],
                'company'=> [
                    'format' => [
                        'rule'    => 'isEmpty',
                        'message' => 'company must be empty',
                    ],
                ],
            ],
            // array
            [
                'name[]' => [
                    'format' => [
                        'rule'    => 'isEmpty',
                        'negate'  => true,
                        'message' => 'name can not be empty',
                    ],
                ],
                'company[]' => [
                    'format' => [
                        'rule'    => 'isEmpty',
                        'message' => 'company must be empty',
                    ],
                ],
                'nonexistent[]'  => [
                ],
            ],
            // multi-dimensional array
            [
                'data[name][]' => [
                    'format' => [
                        'rule'    => 'isEmpty',
                        'negate'  => true,
                        'message' => 'name can not be empty',
                    ],
                ],
                'data[company][]' => [
                    'format' => [
                        'rule'    => 'isEmpty',
                        'message' => 'company must be empty',
                    ],
                ],
            ],
            // alternative array
            [
                'name[1]' => [
                    'format' => [
                        'rule'    => [[$this, 'callBackTestMethod']],
                        'message' => 'name[1] can not be empty',
                    ],
                ],
                'name[2]' => [
                    'format' => [
                        'rule'    => [[$this, 'callBackTestMethod']],
                        'message' => 'name[2] must be empty',
                    ],
                ],
            ],
            // alternative multi-dimensional array
            [
                'data[][name]' => [
                    'format' => [
                        'rule'    => 'isEmpty',
                        'negate'  => 'true',
                        'message' => 'name can not be empty',
                    ],
                ],
                'data[][company]' => [
                    'format' => [
                        'rule'    => 'isEmpty',
                        'message' => 'company must be empty',
                    ],
                ],
            ],
            // failure data set
            [
                'name' => [
                    'empty' => [
                        'rule'    => 'isEmpty',
                        'message' => 'name can not be empty',
                        'negate'  => true,
                        'last'    => true,
                    ],
                    'too_short' => [
                        'rule'    => ['minLength', 5],
                        'message' => 'name must be at least 5 chars',
                    ],
                ],
                'company' => [
                    'empty' => [
                        'rule'    => 'isEmpty',
                        'message' => 'company can not be empty',
                        'negate'  => true,
                        'final'   => true,
                    ],
                ],
            ],
        ];

        $data_sets = [
            [
                'name'    => 'Firstname Lastname',
                'company' => '',
            ],
            [
                'name' => [
                    'Firstname Lastname',
                    'Secondname Lastname',
                    'Thirdname Lastname',
                ],
                'company' => [
                    '',
                    '',
                    '',
                ],
            ],
            [
                'data' => [
                    'name' => [
                        'Firstname Lastname',
                        'Secondname Lastname',
                        'Thirdname Lastname',
                    ],
                    'company' => [
                        '',
                        '',
                        '',
                    ],
                ],
            ],
            [
                'name' => [
                    '1' => 'Firstname Lastname',
                    '2' => 'Secondname Lastname',
                ],
            ],
            [
                'data' => [
                    [
                        'name'    => 'Firstname Lastname',
                        'company' => '',
                    ],
                    [
                        'name'    => 'Secondname Lastname',
                        'company' => '',
                    ],
                    [
                        'name'    => 'Thirdname Lastname',
                        'company' => '',
                    ],
                ],
            ],
            [
                'name' => 'Name',
            ],
        ];
        $result_sets = [
            true,
            true,
            true,
            true,
            true,
            false,
        ];

        $data = [];
        foreach ($rule_sets as $i => $set) {
            $data[] = [$rule_sets[$i], $data_sets[$i], $result_sets[$i]];
        }

        return $data;
    }

    public function callBackTestMethod($value)
    {
        return true;
    }
}
