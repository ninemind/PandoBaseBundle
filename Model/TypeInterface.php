<?php
namespace BlackBoxCode\Pando\Bundle\BaseBundle\Model;

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
