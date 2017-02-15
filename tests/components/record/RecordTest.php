<?php

class RecordTest extends PHPUnit_Framework_TestCase
{
    private $Record;

    public function setUp()
    {
        $this->Record = new Advandz\Component\Record();
    }

    /**
     * @covers Record::keywordValue
     */
    public function testKeywordValue()
    {
        $this->assertSame('DEFAULT', $this->Record->keywordValue('DEFAULT')->keyword);
        $this->assertSame('INDEX', $this->Record->keywordValue('INDEX')->keyword);
    }

    /**
     * @covers Record::setField
     */
    public function testSetField()
    {
        $this->assertInstanceOf('Advandz\\Component\\Record', $this->Record->setField('name', ['type' => 'int', 'size' => 10, 'unsigned' => true], true));
        $this->assertInstanceOf('Advandz\\Component\\Record', $this->Record->setField('name', null, false));
    }

    /**
     * @covers Record::setKey
     */
    public function testSetKey()
    {
        $this->assertNull($this->Record->setKey([], 'index'));
        $this->assertInstanceOf('Advandz\\Component\\Record', $this->Record->setKey(['id'], 'primary', true, 'id', true));
        $this->assertInstanceOf('Advandz\\Component\\Record', $this->Record->setKey(['id'], 'primary', true, null, false));
    }

    /**
     * @covers Record::create
     * @covers Record::buildQuery
     * @covers Record::buildTables
     * @covers Record::buildFields
     * @covers Record::buildTableOptions
     */
    public function testCreate()
    {
        $pdo_statement = $this->getMockBuilder('PDOStatement')
            ->getMock();

        $query  = 'CREATE TABLE `table_name` (`id` int(10) UNSIGNED  NOT NULL AUTO_INCREMENT, `field1` varchar(32) NULL, PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci';
        $record = $this->getQueryMock($query, $params = [], $pdo_statement);
        $record
            ->setField('id', ['type' => 'int', 'size' => 10, 'unsigned' => true, 'auto_increment' => true])
            ->setField('field1', ['type' => 'varchar', 'size' => 32, 'default' => null, 'is_null' => true])
            ->setKey(['id'], 'primary')
            ->create('table_name');
    }

    /**
     * @covers Record::alter
     * @covers Record::buildQuery
     * @covers Record::buildTables
     * @covers Record::buildFields
     */
    public function testAlter()
    {
        $pdo_statement = $this->getMockBuilder('PDOStatement')
            ->getMock();

        $query  = 'ALTER TABLE `table_name` DROP `id`, DROP `field1`, DROP PRIMARY KEY ';
        $record = $this->getQueryMock($query, $params = [], $pdo_statement);
        $record
            ->setField('id', null, false)
            ->setField('field1', null, false)
            ->setKey(['id'], 'primary', null, false)
            ->alter('table_name');
    }

    /**
     * @covers Record::truncate
     * @covers Record::buildQuery
     * @covers Record::buildTables
     */
    public function testTruncate()
    {
        $pdo_statement = $this->getMockBuilder('PDOStatement')
            ->getMock();

        $query  = 'TRUNCATE TABLE `table_name`';
        $record = $this->getQueryMock($query, null, $pdo_statement);
        $record->truncate('table_name');
    }

    /**
     * @covers Record::drop
     * @covers Record::buildQuery
     * @covers Record::buildTables
     */
    public function testDrop()
    {
        $pdo_statement = $this->getMockBuilder('PDOStatement')
            ->getMock();

        $query  = 'DROP TABLE `table_name`';
        $record = $this->getQueryMock($query, null, $pdo_statement);
        $record->drop('table_name');

        $query  = 'DROP TABLE IF EXISTS `table_name`';
        $record = $this->getQueryMock($query, null, $pdo_statement);
        $record->drop('table_name', true);
    }

    /**
     * @covers Record::set
     * @covers Record::buildQuery
     * @covers Record::buildTables
     */
    public function testSet()
    {
        $this->assertInstanceOf('Advandz\\Component\\Record', $this->Record->set('field', 'value'));
        $this->assertInstanceOf('Advandz\\Component\\Record', $this->Record->set('field', $this->Record->keywordValue('DEFAULT')));
    }

    /**
     * @covers Record::insert
     * @covers Record::setFields
     * @covers Record::buildQuery
     * @covers Record::buildValues
     * @covers Record::escapeField
     * @covers Record::escapeFieldMatches
     * @covers Record::escapeTableField
     */
    public function testInsert()
    {
        $pdo_statement = $this->getMockBuilder('PDOStatement')
            ->getMock();

        $query  = 'INSERT INTO `table_name` (`field1`, `field2`) VALUES (?, ?)';
        $record = $this->getQueryMock($query, null, $pdo_statement);

        $record->set('field1', 1)
            ->set('field2', 2)
            ->insert('table_name');
    }

    /**
     * @covers Record::update
     * @covers Record::buildQuery
     * @covers Record::buildTables
     * @covers Record::buildValuePairs
     * @covers Record::buildWhere
     * @covers Record::buildLimit
     */
    public function testUpdate()
    {
        $pdo_statement = $this->getMockBuilder('PDOStatement')
            ->getMock();

        $query  = 'UPDATE `table_name` SET `field1`=?, `field2`=? WHERE `field1`=?';
        $record = $this->getQueryMock($query, null, $pdo_statement);

        $record->set('field1', 1)
            ->set('field2', 2)
            ->where('field1', '=', 3)
            ->update('table_name');
    }

    /**
     * @covers Record::delete
     * @covers Record::buildQuery
     * @covers Record::buildColumns
     * @covers Record::buildTables
     * @covers Record::buildWhere
     * @covers Record::buildLimit
     */
    public function testDelete()
    {
        $pdo_statement = $this->getMockBuilder('PDOStatement')
            ->getMock();

        $query  = 'DELETE  FROM `table_name` WHERE `field1`=?';
        $record = $this->getQueryMock($query, null, $pdo_statement);

        $record->from('table_name')
            ->where('field1', '=', 1)
            ->delete();

        $query  = 'DELETE `table_name`.* FROM `table_name` INNER JOIN `other_table` ON `other_table`.`id`=`table_name`.`id`';
        $record = $this->getQueryMock($query, null, $pdo_statement);

        $record->from('table_name')
            ->innerJoin('other_table', 'other_table.id', '=', 'table_name.id', false)
            ->delete(['table_name.*']);
    }

    /**
     * @covers Record::select
     */
    public function testSelect()
    {
        $this->assertInstanceOf('Advandz\\Component\\Record', $this->Record->select());
    }

    /**
     * @covers Record::from
     */
    public function testFrom()
    {
        $this->assertInstanceOf('Advandz\\Component\\Record', $this->Record->from('table'));
    }

    /**
     * @covers Record::join
     * @covers Record::buildJoin
     * @covers Record::buildConditionals
     * @covers Record::buildConditional
     */
    public function testJoin()
    {
        $this->assertInstanceOf('Advandz\\Component\\Record', $this->Record->join('table2', 'table1.field', '=', 'table2.field'));
    }

    /**
     * @covers Record::leftJoin
     * @covers Record::buildJoin
     * @covers Record::buildConditionals
     * @covers Record::buildConditional
     */
    public function testLeftJoin()
    {
        $this->assertInstanceOf('Advandz\\Component\\Record', $this->Record->leftJoin('table2', 'table1.field', '=', 'table2.field'));
    }

    /**
     * @covers Record::rightJoin
     * @covers Record::buildJoin
     * @covers Record::buildConditionals
     * @covers Record::buildConditional
     */
    public function testRightJoin()
    {
        $this->assertInstanceOf('Advandz\\Component\\Record', $this->Record->rightJoin('table2', 'table1.field', '=', 'table2.field'));
    }

    /**
     * @covers Record::innerJoin
     * @covers Record::buildJoin
     * @covers Record::buildConditionals
     * @covers Record::buildConditional
     */
    public function testInnerJoin()
    {
        $this->assertInstanceOf('Advandz\\Component\\Record', $this->Record->innerJoin('table2', 'table1.field', '=', 'table2.field'));
    }

    /**
     * @covers Record::on
     * @covers Record::setConditional
     */
    public function testOn()
    {
        $this->assertInstanceOf('Advandz\\Component\\Record', $this->Record->on('table1.field', '=', 'table2.field')->innerJoin('table2'));
    }

    /**
     * @covers Record::orOn
     * @covers Record::setConditional
     */
    public function testOrOn()
    {
        $this->assertInstanceOf('Advandz\\Component\\Record', $this->Record->orOn('table1.field', '=', 'table2.field')->innerJoin('table2'));
    }

    /**
     * @covers Record::where
     * @covers Record::setConditional
     */
    public function testWhere()
    {
        $this->assertInstanceOf('Advandz\\Component\\Record', $this->Record->where('table1.field', '=', 'table2.field'));
    }

    /**
     * @covers Record::orWhere
     * @covers Record::setConditional
     */
    public function testOrWhere()
    {
        $this->assertInstanceOf('Advandz\\Component\\Record', $this->Record->orWhere('table1.field', '=', 'table2.field'));
    }

    /**
     * @covers Record::duplicate
     * @covers Record::setConditional
     */
    public function testDuplicate()
    {
        $this->assertInstanceOf('Advandz\\Component\\Record', $this->Record->duplicate('table1.field', '=', 'new value'));
    }

    /**
     * @covers Record::like
     * @covers Record::setConditional
     */
    public function testLike()
    {
        $this->assertInstanceOf('Advandz\\Component\\Record', $this->Record->like('table1.field', '%value%'));
    }

    /**
     * @covers Record::notLike
     * @covers Record::setConditional
     */
    public function testNotLike()
    {
        $this->assertInstanceOf('Advandz\\Component\\Record', $this->Record->notLike('table1.field', '%value%'));
    }

    /**
     * @covers Record::orLike
     * @covers Record::setConditional
     */
    public function testOrLike()
    {
        $this->assertInstanceOf('Advandz\\Component\\Record', $this->Record->orLike('table1.field', '%value%'));
    }

    /**
     * @covers Record::orNotLike
     * @covers Record::setConditional
     */
    public function testOrNotLike()
    {
        $this->assertInstanceOf('Advandz\\Component\\Record', $this->Record->orNotLike('table1.field', '%value%'));
    }

    /**
     * @covers Record::having
     * @covers Record::setConditional
     */
    public function testHaving()
    {
        $this->assertInstanceOf('Advandz\\Component\\Record', $this->Record->having('table1.field', '=', 'table2.field'));
    }

    /**
     * @covers Record::orHaving
     * @covers Record::setConditional
     */
    public function testOrHaving()
    {
        $this->assertInstanceOf('Advandz\\Component\\Record', $this->Record->orHaving('table1.field', '=', 'table2.field'));
    }

    /**
     * @covers Record::group
     */
    public function testGroup()
    {
        $this->assertInstanceOf('Advandz\\Component\\Record', $this->Record->group('table1.field'));
        $this->assertInstanceOf('Advandz\\Component\\Record', $this->Record->group(['table1.field', 'table1.field2']));
    }

    /**
     * @covers Record::order
     */
    public function testOrder()
    {
        $this->assertInstanceOf('Advandz\\Component\\Record', $this->Record->order(['table1.field' => 'asc']));
        $this->assertInstanceOf('Advandz\\Component\\Record', $this->Record->order(['table1.field', 'table1.field2']));
    }

    /**
     * @covers Record::limit
     */
    public function testLimit()
    {
        $this->assertInstanceOf('Advandz\\Component\\Record', $this->Record->limit(30));
    }

    /**
     * @covers Record::open
     */
    public function testOpen()
    {
        $this->assertInstanceOf('Advandz\\Component\\Record', $this->Record->open());
    }

    /**
     * @covers Record::close
     */
    public function testClose()
    {
        $this->assertInstanceOf('Advandz\\Component\\Record', $this->Record->open()->close());
        $this->assertInstanceOf('Advandz\\Component\\Record', $this->Record->open()->where('table1.field', '=', 'table2.field')->close('where'));
        $this->assertInstanceOf('Advandz\\Component\\Record', $this->Record->open()->on('table1.field', '=', 'table2.field')->close('on'));
        $this->assertInstanceOf('Advandz\\Component\\Record', $this->Record->open()->having('table1.field', '=', 'table2.field')->close('having'));
        $this->assertInstanceOf('Advandz\\Component\\Record', $this->Record->open()->duplicate('table1.field', '=', 'new value')->close('duplicate'));
    }

    /**
     * @covers Record::appendValues
     */
    public function testAppendValues()
    {
        $values = [1, 2, 3, 'x', 'y', 'z'];
        $this->assertInstanceOf('Advandz\\Component\\Record', $this->Record->appendValues($values));
        $this->assertSame($values, $this->Record->values);

        $more_values = ['a', 'b', 'c'];
        $this->Record->appendValues($more_values);
        $this->assertSame(array_merge($values, $more_values), $this->Record->values);
    }

    /**
     * @covers Record::reset
     */
    public function testReset()
    {
        $record = clone $this->Record;
        $this->Record->appendValues(['table1.field', 'table2.field']);
        $this->assertNotSame($record->values, $this->Record->values);
        $this->Record->reset();
        $this->assertSame($record->values, $this->Record->values);
    }

    /**
     * @covers Record::quoteIdentifier
     * @dataProvider quoteIdentifierProvider
     */
    public function testQuoteIdentifier($identifier, $result)
    {
        $this->assertSame($result, $this->Record->quoteIdentifier($identifier));
    }

    /**
     * Dataprovider for testQuoteIdentifier.
     */
    public function quoteIdentifierProvider()
    {
        return [
            [['table', 'field'], '`table`.`field`'],
            ['table.field', '`table`.`field`'],
            ['field', '`field`'],
        ];
    }

    public function testSetReturnRecordInstance()
    {
        $this->assertInstanceOf('Advandz\\Component\\Record', $this->Record->set('field', 'value'));
    }

    /**
     * Generates a Record mock with Record::query and Record::reset mocked.
     *
     * @param  string $query  The SQL before substitution
     * @param  array  $params The parameters to substitute
     * @return object
     */
    protected function getQueryMock($query, $params = [], $return = null)
    {
        $record = $this->getMockBuilder('Advandz\\Component\\Record')
            ->disableOriginalConstructor()
            ->setMethods(['query', 'reset'])
            ->getMock();

        if ($params !== null) {
            $record->expects($this->once())
                ->method('query')
                ->with($this->equalTo($query),
                    $this->equalTo($params))
                ->will($this->returnValue($return));
        } else {
            $record->expects($this->once())
                ->method('query')
                ->with($this->equalTo($query))
                ->will($this->returnValue($return));
        }

        $record->expects($this->once())
            ->method('reset');

        return $record;
    }
}
