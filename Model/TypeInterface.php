<?php
namespace BlackBoxCode\Pando\BaseBundle\Model;

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
