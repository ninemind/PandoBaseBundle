<?php
namespace BlackBoxCode\Pando\Bundle\BaseBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

trait BaseTrait
{
    /**
     * @var string
     *
     * @ORM\Id
     * @ORM\Column(type="guid")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param $propertyName
     */
    private function instantiateArrayCollection($propertyName) {
        $this->$propertyName = new ArrayCollection();
    }
}
