<?php namespace Fs;

use Illuminate\Database\Eloquent\Relations\Relation;
use ReflectionClass;
use ReflectionMethod;
use PHPUnit_Framework_Assert;
use Minime\Annotations\Reader;
/**
* Checka se o {$model} está
* usando a cardinalidade {$tipo}
* e relacionado com {$modelRelacionado}
*/
class Relationship
{
    protected $reflectionModel;
    protected $type;
    protected $relatedModel;
    protected $disableReverseCheck;

    protected $map = [
        self::HAS_MANY => self::BELONGS_TO,
        self::BELONGS_TO => self::HAS_MANY,
        self::HAS_ONE => self::HAS_ONE
    ];

    const BELONGS_TO        = 'Illuminate\Database\Eloquent\Relations\BelongsTo';
    const HAS_MANY          = 'Illuminate\Database\Eloquent\Relations\HasMany';
    const HAS_ONE           = 'Illuminate\Database\Eloquent\Relations\HasOne';

    public function __construct(ReflectionClass $reflection, $type, $relatedModel, $disableReverseCheck)
    {
        $this->reflectionModel = $reflection;
        $this->type = $type;
        $this->relatedModel = $relatedModel;
        $this->disableReverseCheck = $disableReverseCheck;
    }

    /**
     * Verifica relacionamento
     * 
     * @param  Illuminate\Database\Eloquent\Model $model
     * @param  BELONGS_TO|HAS_MANY|HAS_ONE $tipo
     * @param  string $modelRelacionado
     * @return void
     */
    public static function check($model, $type, $relatedModel, $disableReverseCheck = false)
    {
        return (new static(new ReflectionClass($model), $type, $relatedModel, $disableReverseCheck))->verify();
    }

    protected function verify()
    {
        foreach ($this->reflectionModel->getmethods() as $method) {
            if ($this->isARelation($method)) {

                $relationship = $method->invoke( $this->reflectionModel->newInstance() );

                if ($this->verifyRelation($method, $relationship)) {

                    PHPUnit_Framework_Assert::assertTrue(true);

                    if (!$this->disableReverseCheck) {
                        $this->checkReverseRelationship($relationship);
                    }

                    return true;
                }
            }

        }

        PHPUnit_Framework_Assert::fail( sprintf("%s nao possui metodo com @return %s definindo um relacionamento do tipo: %s com %s",
            $this->reflectionModel->getName(), $this->type, $this->type, $this->relatedModel) );
    }

    protected function checkReverseRelationship(Relation $relationship)
    {
        $model = get_class($this->reflectionModel->newInstance());
        $relatedModel = $relationship->getRelated();

        if($model !== get_class($relatedModel)) {
            static::check($relatedModel, $this->map[$this->type], $model, true);
        }

    }

    protected function isARelation(ReflectionMethod $method)
    {

        $relations = [static::HAS_MANY, static::HAS_ONE, static::BELONGS_TO];

        return in_array($this->getReturnAnnotation($method), $relations);
    }

    protected function verifyRelation(ReflectionMethod $method, Relation $relationship)
    {
        $expectedRelatedModel = $this->relatedModel === get_class($relationship->getRelated());

        $expectedRelationName = get_class($relationship) === $this->type;

        $expectedReturnAnnotation = $this->getReturnAnnotation($method) === $this->type;

        return $expectedRelationName && $expectedRelatedModel && $expectedReturnAnnotation;
    }

    protected function getReturnAnnotation(ReflectionMethod $method)
    {
        $annotationsBag = (Reader::createFromDefaults()->getMethodAnnotations($method->class, $method->getName()));

        return $annotationsBag->get('return');
    }
}