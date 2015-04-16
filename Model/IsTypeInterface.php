<?php
namespace BlackBoxCode\Pando\Bundle\BaseBundle\Model;

interface IsTypeInterface extends HasIdInterface
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
