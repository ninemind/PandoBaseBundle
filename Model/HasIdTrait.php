<?php
namespace BlackBoxCode\Pando\Bundle\BaseBundle\Model;

use Doctrine\ORM\Mapping as ORM;

trait HasIdTrait
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
}
