<?php
namespace BlackBoxCode\Pando\Bundle\BaseBundle\Model;

use Doctrine\ORM\Mapping as ORM;

interface TypeInterface extends IdInterface
{
    /**
     * @param string $name
     * @return $this
     */
    public function setName($name);

    /**
     * @return string
     */
    public function getName();
}
