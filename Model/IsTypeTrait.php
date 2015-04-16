<?php
namespace BlackBoxCode\Pando\Bundle\BaseBundle\Model;

use Doctrine\ORM\Mapping as ORM;

trait IsTypeTrait
{
    use HasIdTrait;

    /**
     * @var string
     *
     * @ORM\Column(type="string", unique=true)
     */
    protected $name;

    /**
     * {@inheritdoc}
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }
}
