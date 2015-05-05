<?php
namespace BlackBoxCode\Pando\Bundle\BaseBundle\Tools;

use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\DependencyInjection\ContainerInterface;

class EntityGenerator
{
    /** @var string */
    private static $spaces = '    ';

    /** @var string */
    private static $template =
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

    /** @var string */
    private $namespace;


    /**
     * @param string $namespace
     */
    public function __construct($namespace)
    {
        $this->namespace = $namespace;
    }

    /**
     * Generates a Doctrine 2 entity class from the given EntityMetadata instance
     *
     * @param EntityMetadata $meta
     * @return string
     */
    public function generateEntityClass(EntityMetadata $meta)
    {
        $placeholders = array(
            '<namespace>',
            '<classAnnotations>',
            '<className>',
            '<implements>',
            '<traits>'
        );

        $replacements = array(
            $this->namespace,
            $this->generateClassAnnotations($meta->getTraits()),
            $meta->getClassName(),
            $this->generateImplements($meta->getInterfaces()),
            $this->generateTraitStatements($meta->getTraits())
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
        return count($interfaces) ? ' implements ' . implode(', ', $interfaces) : '';
    }

    /**
     * @param array $traits
     * @return string
     */
    protected function generateTraitStatements($traits)
    {
        $traitStatements = '';
        foreach ($traits as $trait) {
            $traitStatements .= sprintf("<spaces>use %s;\n", $trait);
        }

        return substr($traitStatements, 0, -1);
    }

    /**
     * @param array $traits
     * @return string
     */
    protected function generateClassAnnotations($traits)
    {
        $annotationArray = $this->buildClassAnnotationArray($traits);
        return $this->buildClassAnnotationString($annotationArray);
    }

    protected function buildClassAnnotationArray($traits)
    {
        $annotations = array();

        $reader = new AnnotationReader();
        foreach ($traits as $trait) {
            $annotations = array_merge(
                $annotations,
                $this->parseTraitAnnotations(
                    $reader->getClassAnnotations(new \ReflectionClass($trait))
                )
            );
        }

        if (!array_key_exists('@ORM\\Entity', $annotations)) {
            throw new \InvalidArgumentException('Not an entity');
        }

        return $annotations;
    }

    /**
     * @param array $annotations
     * @return array
     */
    private function parseTraitAnnotations($annotations)
    {
        $annotationArray = array();

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

        return $annotationArray;
    }

    /**
     * @param array $annotationArray
     * @return string
     */
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

    /**
     * @param EntityMetadata $meta
     * @param string $outputDirectory
     */
    public function writeEntityToFile(EntityMetadata $meta, $outputDirectory)
    {
        $path = $outputDirectory . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $this->namespace) . DIRECTORY_SEPARATOR . $meta->getClassName() . self::$extension;

        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        if (file_exists($path)) {
            unlink($path);
        }

        file_put_contents($path, $this->generateEntityClass($meta));
    }

    /**
     * @param string $entityName
     * @param array $dependencies
     * @return EntityMetadata
     */
    public function createEntityMetadata($entityName, $dependencies)
    {
        $meta = new EntityMetadata();
        $meta->setClassName($entityName);

        foreach ($dependencies as $classType => $classes) {
            $method = 'add' . $classType;
            foreach ($classes as $class) {
                $meta->$method('\\' . $class);
            }
        }

        return $meta;
    }
}
