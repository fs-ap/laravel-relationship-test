<?php

use Fs\Relationship;
use Illuminate\Database\Capsule\Manager;    

/**
 * RelationshipTest
 *
 * @group group
 */
class RelationshipTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @beforeClass
     */
    public static function setUpClass()
    {
        $capsule = new Manager;

        $capsule->bootEloquent();

        $capsule->addConnection([ 'driver' => 'sqlite', 'database' => ':memory:' ]);

        eval("class Author extends Illuminate\Database\Eloquent\Model {
            
            /**
             * @return Illuminate\Database\Eloquent\Relations\HasMany
             */
            public function comments() { return \$this->hasMany(Comment::class); }
        }");

        eval("class Comment extends Illuminate\Database\Eloquent\Model {

            /**
             * @return Illuminate\Database\Eloquent\Relations\BelongsTo
             */
            public function author() { return \$this->belongsTo(Author::class); }
        }");
    }
    /**
     * Check if the class in first param has a method that defines relation of type Relationship::HAS_MANY
     * and the second class has a relation of type Relationship::BELONGS_TO
     */
    public function testCheckBidirecionalRelation()
    {
        $this->assertTrue(Relationship::check(Author::class, Relationship::HAS_MANY, Comment::class));
    }

    /**
     * Check if the first param has a method that defines relation of type Relationship::HAS_MANY
     * and NO CHECK the reverse relation to Relationship::HAS_MANY
     * @covers class::()
     */
    public function testCheckDirecionalRelation()
    {
        //there is not in class Author method to define relation to books
        eval("class Book extends Illuminate\Database\Eloquent\Model {

            /**
             * @return Illuminate\Database\Eloquent\Relations\HasMany
             */
            public function authors() { return \$this->hasMany(Author::class); }
        }");

        // last param ignores the reverse check
        $this->assertTrue(Relationship::check(Book::class, Relationship::HAS_MANY, Author::class, true));
    }

    /**
     * Check if the class in first param has a method that defines relation of type Relationship::BELONGS_TO
     * and the second class has a relation of type Relationship::HAS_MANY
     *
     * Should fail with this exception
     * @expectedException \PHPUnit_Framework_AssertionFailedError
     */
    public function testCheckFailOnVerificationOFRelation()
    {
        //the Comment has not has many authors
        Relationship::check(Comment::class, Relationship::HAS_MANY, Author::class);
    }

    /**
     * Check if this @return have a valid Value
     * 
     * @expectedException \PHPUnit_Framework_AssertionFailedError
     */
    public function testCheckFailOnReturnAnnotationIsInvalid()
    {
        //the @return annotation have a invalid value
        eval("class Role extends Illuminate\Database\Eloquent\Model {

            /**
             * This @return should be HasMany
             * @return Illuminate\Database\Eloquent\Relations\BelongsTo
             */
            public function authors() { return \$this->hasMany(Author::class); }
        }");

        $this->assertTrue(Relationship::check(Role::class, Relationship::HAS_MANY, Author::class));
    }
}
