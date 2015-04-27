<?php
namespace BlackBoxCode\Pando\Bundle\BaseBundle\Tools;

use Doctrine\Common\Annotations\AnnotationReader;

class EntityGenerator
{
    /** @var string */
    protected static $spaces = '    ';

    /** @var string */
    protected static $template =
'<?php
namespace <namespace>;

use Doctrine\ORM\Mapping as ORM;

/**
<classAnnotations>
 */
class <className><implements>
{
<traits>
}';

    /** @var string */
    protected static $extension = '.php';


    /**
     * Generates a Doctrine 2 entity class from the given EntityModel instance
     *
     * @param EntityModel $model
     * @return string
     */
    public function generateEntityClass(EntityModel $model)
    {
        $placeholders = array(
            '<namespace>',
            '<classAnnotations>',
            '<className>',
            '<implements>',
            '<traits>'
        );

        $replacements = array(
            $model->getNamespace(),
            $this->generateClassAnnotations($model->getTraits()),
            $model->getClassName(),
            $this->generateImplements($model->getInterfaces()),
            $this->generateTraitStatements($model->getTraits())
        );

        $code = str_replace($placeholders, $replacements, self::$template);
        return str_replace('<spaces>', self::$spaces, $code);
    }

    /**
     * @param array $interfaces
     * @return string
     */
    protected function generateImplements($interfaces)
    {
        return ' implements ' . implode(', ', $interfaces);
    }

    protected function generateTraitStatements($traits)
    {
        $traitStatements = '';
        foreach ($traits as $trait) {
            $traitStatements .= sprintf("<spaces>use %s;\n", $trait);
        }

        return substr($traitStatements, 0, -1);
    }

    protected function generateClassAnnotations($traits)
    {
        $annotationArray = $this->buildClassAnnotationArray($traits);
        return $this->buildClassAnnotationString($annotationArray);
    }

    protected function buildClassAnnotationArray($traits)
    {
        $annotationArray = array();

        $reader = new AnnotationReader();
        foreach ($traits as $trait) {
            $annotations = $reader->getClassAnnotations(new \ReflectionClass($trait));

            foreach ($annotations as $annotation) {
                $namespace = explode('\\', get_class($annotation));
                $root = array_pop($namespace);

                switch ($root) {
                    case 'Table':
                        if (!is_null($annotation->indexes)) {
                            foreach ($annotation->indexes as $index) {
                                $annotationArray['@ORM\\' . $root]['indexes'][] = '@ORM\Index(columns={"' . implode('", "', $index->columns) . '"})';
                            }
                        }

                        if (!is_null($annotation->uniqueConstraints)) {
                            foreach ($annotation->uniqueConstraints as $uniqueConstraint) {
                                $annotationArray['@ORM\\' . $root]['uniqueConstraints'][] = '@ORM\UniqueConstraint(columns={"' . implode('", "', $uniqueConstraint->columns) . '"})';
                            }
                        }
                        break;

                    default:
                        $annotationArray['@ORM\\' . $root] = null;
                }
            }
        }

        if (!array_key_exists('@ORM\\Entity', $annotationArray)) {
            throw new \InvalidArgumentException('Not an entity');
        }

        return $annotationArray;
    }

    protected function buildClassAnnotationString($annotationArray)
    {
        $annotationString = "";
        foreach ($annotationArray as $key => $keys) {
            $annotationString .= ' * ' . $key;
            if (count($keys) > 0) {
                $annotationString .= "(";
                foreach ($keys as $k => $values) {
                    $annotationString .= $k . '={' . implode(',', $values) . '},';
                }
                $annotationString = substr($annotationString, 0, -1) . ')';
            }
            $annotationString .= "\n";
        }

        return substr($annotationString, 0, -1);
    }

    public function writeEntityToFile(EntityModel $model, $outputDirectory)
    {
        $path = $outputDirectory . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $model->getNamespace()) . DIRECTORY_SEPARATOR . $model->getClassName() . self::$extension;

        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        if (file_exists($path)) {
            unlink($path);
        }

        file_put_contents($path, $this->generateEntityClass($model));
    }
}
