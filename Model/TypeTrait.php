<?php
namespace BlackBoxCode\Pando\BaseBundle\Model;

use Doctrine\ORM\Mapping as ORM;

trait TypeTrait
{
    use IdTrait;

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
