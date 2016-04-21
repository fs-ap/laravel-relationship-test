# Fs-ap/Laravel-relationship-test
==================

Enhance the unit test of relationship in model to projects in laravel

## Composer Installation

```json
{
   "repositories": [
         {
         "type": "vcs",
         "url": "https://gitlab.com/fs-ap/laravel-relationship-test.git"
         }
    ],
  "require-dev": {
    "fs-ap/laravel-relationship-test": "~1.0"
  }
}
```
Or

Through terminal: `composer require --dev fs-ap/laravel-relationship-test:~1.0`

## Usage

```php
<?php

use Fs\Relationship;

	class AuthorModelTest extends PHPUnit_Framework_TestCase {
    
        /**
         * Check if the class in first param has a method that defines relation of type Relationship::HAS_MANY
         * and the second class has a relation of type Relationship::BELONGS_TO
         */
        public function testAuthorCanHaveManyComments()
        {
            Relationship::check(Author::class, Relationship::HAS_MANY, Comment::class));
        }
    }
```

## Explain

This feature checks bidirectional relation between models through of  ```@return``` annotations on method that defines the relation

```php
<?php

	class Author extends Illuminate\Database\Eloquent\Model {
    
        /**
		* @return Illuminate\Database\Eloquent\Relations\HasMany
        */
        public function comments() { return \$this->hasMany(Comment::class); }
    }
```
And 

```php
<?php

	class Comment extends Illuminate\Database\Eloquent\Model {

        /**
        * @return Illuminate\Database\Eloquent\Relations\BelongsTo
        */
        public function author() { return \$this->belongsTo(Author::class); }
    }
```

## Map of bidirectional check

| Relation | Mapped to |
| ------ | ----------- |
| Relationship::HAS_MANY   | Relationship::BELONGS_TO |
| Relationship::BELONGS_TO | Relationship::HAS_MANY |
| Relationship::HAS_ONE    |Relationship::HAS_ONE |

## Disable bidirectional check

```php
<?php

Relationship::check(Author::class, Relationship::HAS_MANY, Comment::class, true);
```

This checks only if the ```Author``` has many ```Comment``` independent if the relation has defined in ```Comment``` class