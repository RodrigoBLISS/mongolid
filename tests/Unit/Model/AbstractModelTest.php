<?php
namespace Mongolid\Model;

use Mockery as m;
use MongoDB\BSON\Persistable;
use MongoDB\BSON\Serializable;
use MongoDB\BSON\Type;
use MongoDB\BSON\Unserializable;
use MongoDB\Driver\WriteConcern;
use Mongolid\Cursor\CursorInterface;
use Mongolid\Model\Exception\NoCollectionNameException;
use Mongolid\Query\Builder;
use Mongolid\TestCase;
use stdClass;

class AbstractModelTest extends TestCase
{
    /**
     * @var AbstractModel
     */
    protected $model;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();
        $this->model = new class() extends AbstractModel
        {
            /**
             * {@inheritdoc}
             */
            protected $collection = 'mongolid';

            public function unsetCollection()
            {
                unset($this->collection);
            }
        };
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        unset($this->model);
        parent::tearDown();
    }

    public function testShouldImplementModelTraits()
    {
        // Actions
        $result = array_keys(class_uses(AbstractModel::class));

        // Assertions
        $this->assertSame(
            [HasAttributesTrait::class, HasRelationsTrait::class],
            $result
        );
    }

    public function testShouldImplementModelInterface()
    {
        // Actions
        $result = array_keys(class_implements(AbstractModel::class));

        // Assertions
        $this->assertSame(
            [
                ModelInterface::class,
                Unserializable::class,
                Serializable::class,
                Type::class,
                Persistable::class,
                HasAttributesInterface::class,
            ],
            $result
        );
    }

    public function testShouldSave()
    {
        // Set
        $builder = $this->instance(Builder::class, m::mock(Builder::class));

        // Expectations
        $builder->expects()
            ->save($this->model, ['writeConcern' => new WriteConcern(1)])
            ->andReturn(true);

        // Actions
        $result = $this->model->save();

        // Assertions
        $this->assertTrue($result);
    }

    public function testShouldInsert()
    {
        // Set
        $builder = $this->instance(Builder::class, m::mock(Builder::class));

        // Expectations
        $builder->expects()
            ->insert($this->model, ['writeConcern' => new WriteConcern(1)])
            ->andReturn(true);

        // Actions
        $result = $this->model->insert();

        // Assertions
        $this->assertTrue($result);
    }

    public function testShouldUpdate()
    {
        // Set
        $builder = $this->instance(Builder::class, m::mock(Builder::class));

        // Expectations
        $builder->expects()
            ->update($this->model, ['writeConcern' => new WriteConcern(1)])
            ->andReturn(true);

        // Actions
        $result = $this->model->update();

        // Assertions
        $this->assertTrue($result);
    }

    public function testShouldDelete()
    {
        // Set
        $builder = $this->instance(Builder::class, m::mock(Builder::class));

        // Expectations
        $builder->expects()
            ->delete($this->model, ['writeConcern' => new WriteConcern(1)])
            ->andReturn(true);

        // Actions
        $result = $this->model->delete();

        // Assertions
        $this->assertTrue($result);
    }

    public function testSaveShouldThrowExceptionIfCollectionIsNull()
    {
        // Set
        $this->model->unsetCollection();

        // Expectations
        $this->expectException(NoCollectionNameException::class);
        $this->expectExceptionMessage('Collection name not specified into Model instance');

        // Actions
        $this->model->save();
    }

    public function testUpdateShouldThrowExceptionIfCollectionIsNull()
    {
        // Set
        $this->model->unsetCollection();

        // Expectations
        $this->expectException(NoCollectionNameException::class);
        $this->expectExceptionMessage('Collection name not specified into Model instance');

        // Actions
        $this->model->update();
    }

    public function testInsertShouldThrowExceptionIfCollectionIsNull()
    {
        // Set
        $this->model->unsetCollection();

        // Expectations
        $this->expectException(NoCollectionNameException::class);
        $this->expectExceptionMessage('Collection name not specified into Model instance');

        // Actions
        $this->model->insert();
    }

    public function testDeleteShouldThrowExceptionIfCollectionIsNull()
    {
        // Set
        $this->model->unsetCollection();

        // Expectations
        $this->expectException(NoCollectionNameException::class);
        $this->expectExceptionMessage('Collection name not specified into Model instance');

        // Actions
        $this->model->delete();
    }

    public function testShouldGetWithWhereQuery()
    {
        // Set
        $query = ['foo' => 'bar'];
        $projection = ['some', 'fields'];
        $builder = $this->instance(Builder::class, m::mock(Builder::class));

        $cursor = m::mock(CursorInterface::class);

        // Expectations
        $builder->expects()
            ->where(m::type(get_class($this->model)), $query, $projection)
            ->andReturn($cursor);

        // Actions
        $result = $this->model->where($query, $projection);

        // Assertions
        $this->assertSame($cursor, $result);
    }

    public function testShouldGetAll()
    {
        // Set
        $builder = $this->instance(Builder::class, m::mock(Builder::class));
        $cursor = m::mock(CursorInterface::class);

        // Expectations
        $builder->expects()
            ->all(m::type(get_class($this->model)))
            ->andReturn($cursor);

        // Actions
        $result = $this->model->all();

        // Assertions
        $this->assertSame($cursor, $result);
    }

    public function testShouldGetFirstWithQuery()
    {
        // Set
        $query = ['foo' => 'bar'];
        $projection = ['some', 'fields'];
        $builder = $this->instance(Builder::class, m::mock(Builder::class));

        // Expectations
        $builder->expects()
            ->first(m::type(get_class($this->model)), $query, $projection)
            ->andReturn($this->model);

        // Actions
        $result = $this->model->first($query, $projection);

        // Assertions
        $this->assertSame($this->model, $result);
    }

    public function testShouldGetFirstOrFail()
    {
        // Set
        $builder = $this->instance(Builder::class, m::mock(Builder::class));
        $query = ['foo' => 'bar'];
        $projection = ['some', 'fields'];

        // Expectations
        $builder->expects()
            ->firstOrFail(m::type(get_class($this->model)), $query, $projection)
            ->andReturn($this->model);

        // Actions
        $result = $this->model->firstOrFail($query, $projection);

        // Assertions
        $this->assertSame($this->model, $result);
    }

    public function testShouldGetFirstOrNewAndReturnExistingModel()
    {
        // Set
        $builder = $this->instance(Builder::class, m::mock(Builder::class));
        $id = 123;

        // Expectations
        $builder->expects()
            ->first(m::type(get_class($this->model)), $id, [])
            ->andReturn($this->model);

        // Actions
        $result = $this->model->firstOrNew($id);

        // Assertions
        $this->assertSame($this->model, $result);
    }

    public function testShouldGetFirstOrNewAndReturnNewModel()
    {
        // Set
        $builder = $this->instance(Builder::class, m::mock(Builder::class));
        $id = 123;

        // Expectations
        $builder->expects()
            ->first(m::type(get_class($this->model)), $id, [])
            ->andReturn(null);

        // Actions
        $result = $this->model->firstOrNew($id);

        // Assertions
        $this->assertNotEquals($this->model, $result);
    }

    public function testShouldGetBuilder()
    {
        // Set
        $model = new class extends AbstractModel
        {
        };

        // Actions
        $result = $this->callProtected($model, 'getBuilder');

        // Assertions
        $this->assertInstanceOf(Builder::class, $result);
    }

    public function testShouldRaiseExceptionWhenHasNoCollectionAndTryToCallAllFunction()
    {
        // Set
        $model = new class() extends AbstractModel
        {
        };

        // Expectations
        $this->expectException(NoCollectionNameException::class);

        // Actions
        $model->all();
    }

    public function testShouldRaiseExceptionWhenHasNoCollectionAndTryToCallFirstFunction()
    {
        // Set
        $model = new class() extends AbstractModel
        {
        };

        // Expectations
        $this->expectException(NoCollectionNameException::class);

        // Actions
        $model->first();
    }

    public function testShouldRaiseExceptionWhenHasNoCollectionAndTryToCallWhereFunction()
    {
        // Set
        $model = new class() extends AbstractModel
        {
        };

        // Expectations
        $this->expectException(NoCollectionNameException::class);

        // Actions
        $model->where();
    }

    public function testShouldGetCollectionName()
    {
        // Actions
        $result = $this->model->getCollectionName();

        // Assertions
        $this->assertSame('mongolid', $result);
    }

    public function testShouldHaveDefaultWriteConcern()
    {
        // Actions
        $result = $this->model->getWriteConcern();

        // Assertions
        $this->assertSame(1, $result);
    }

    public function testShouldSetWriteConcern()
    {
        // Actions
        $this->model->setWriteConcern(0);
        $result = $this->model->getWriteConcern();

        // Assertions
        $this->assertSame(0, $result);
    }

    public function testShouldHaveDynamicSetters()
    {
        // Set
        $model = new class() extends AbstractModel
        {
        };

        $childObj = new stdClass();
        $model->name = 'John';
        $model->age = 25;
        $model->child = $childObj;

        // Actions
        $result = $model->getDocumentAttributes();

        // Assertions
        $this->assertSame(
            [
                'name' => 'John',
                'age' => 25,
                'child' => $childObj,
            ],
            $result
        );
    }

    public function testShouldHaveDynamicGetters()
    {
        // Set
        $child = new class() extends AbstractModel
        {
        };
        $model = new class() extends AbstractModel
        {
        };

        // Actions
        $model = $model::fill(
            [
                'name' => 'John',
                'age' => 25,
                'child' => $child,
            ],
            $model
        );

        // Assertions
        $this->assertSame('John', $model->name);
        $this->assertSame(25, $model->age);
        $this->assertSame($child, $model->child);
        $this->assertSame(null, $model->nonexistant);
    }

    public function testShouldCheckIfAttributeIsSet()
    {
        // Set
        $model = new class() extends AbstractModel
        {
        };

        // Actions
        $model = $model::fill(['name' => 'John', 'ignored' => null]);

        // Assertions
        $this->assertTrue(isset($model->name));
        $this->assertFalse(isset($model->nonexistant));
        $this->assertFalse(isset($model->ignored));
    }

    public function testShouldCheckIfMutatedAttributeIsSet()
    {
        // Set
        $model = new class() extends AbstractModel
        {
            /**
             * {@inheritdoc}
             */
            public $mutable = true;

            public function getNameDocumentAttribute()
            {
                return 'John';
            }
        };

        // Assertions
        $this->assertTrue(isset($model->name));
        $this->assertFalse(isset($model->nonexistant));
    }

    public function testShouldUnsetAttributes()
    {
        // Set
        $model = new class() extends AbstractModel
        {
        };
        $model = $model::fill(
            [
                'name' => 'John',
                'age' => 25,
            ]
        );

        // Actions
        unset($model->age);
        $result = $model->getDocumentAttributes();

        // Assertions
        $this->assertSame(['name' => 'John'], $result);
    }

    public function testShouldGetAttributeFromMutator()
    {
        // Set
        $model = new class() extends AbstractModel
        {
            /**
             * {@inheritdoc}
             */
            public $mutable = true;

            public function getShortNameDocumentAttribute()
            {
                return 'Other name';
            }
        };

        // Actions
        $model->short_name = 'My awesome name';
        $result = $model->short_name;

        // Assertions
        $this->assertSame('Other name', $result);
    }

    public function testShouldIgnoreMutators()
    {
        // Set
        $model = new class() extends AbstractModel
        {
            public function getShortNameDocumentAttribute()
            {
                return 'Other name';
            }

            public function setShortNameDocumentAttribute($value)
            {
                return strtoupper($value);
            }
        };

        // Actions
        $model->short_name = 'My awesome name';

        // Assertions
        $this->assertSame('My awesome name', $model->short_name);
    }

    public function testShouldSetAttributeFromMutator()
    {
        // Set
        $model = new class() extends AbstractModel
        {
            /**
             * {@inheritdoc}
             */
            protected $mutable = true;

            public function setShortNameDocumentAttribute($value)
            {
                return strtoupper($value);
            }
        };

        // Actions
        $model->short_name = 'My awesome name';
        $result = $model->short_name;

        // Assertions
        $this->assertSame('MY AWESOME NAME', $result);
    }
}
