<?php
namespace BlackBoxCode\Pando\Bundle\BaseBundle\Tests\DataFixures\ORM;

use BlackBoxCode\Pando\Bundle\BaseBundle\DataFixtures\ORM\LoadTypeData;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Container;

class OneType {
    const A = 'A';
    const B = 'B';

    public function setName($name) {}
}
class One {
    const C = 'C';
}

class LoadTypeDataTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject|LoadTypeData */
    private $mLoadTypeData;

    /** @var \PHPUnit_Framework_MockObject_MockObject|Container */
    private $mContainer;

    /** @var \PHPUnit_Framework_MockObject_MockObject|ObjectManager */
    private $mObjectManager;


    public function setUp()
    {
        $this->mLoadTypeData = $this->getMock(get_class(new LoadTypeData()), ['getEntityList', 'createTypeEntity']);
        $this->mContainer = $this->getMock('Symfony\Component\DependencyInjection\Container');
        $this->mObjectManager = $this->getMock('Doctrine\Common\Persistence\ObjectManager');

        $this->mLoadTypeData->setContainer($this->mContainer);
    }

    /**
     * @test
     */
    public function load()
    {
        $mOneType = $this->getMock('BlackBoxCode\Pando\Bundle\BaseBundle\Tests\DataFixures\ORM\OneType', ['setName']);

        $this->mLoadTypeData
            ->expects($this->once())
            ->method('getEntityList')
            ->willReturn([
                'BlackBoxCode\Pando\Bundle\BaseBundle\Tests\DataFixures\ORM\OneType',
                'BlackBoxCode\Pando\Bundle\BaseBundle\Tests\DataFixures\ORM\One'
            ])
        ;

        $this->mLoadTypeData
            ->expects($this->exactly(2))
            ->method('createTypeEntity')
            ->with('BlackBoxCode\Pando\Bundle\BaseBundle\Tests\DataFixures\ORM\OneType')
            ->willReturn($mOneType)
        ;

        $mOneType
            ->expects($this->exactly(2))
            ->method('setName')
            ->withConsecutive(['A'], ['B'])
        ;

        $this->mObjectManager
            ->expects($this->exactly(2))
            ->method('persist')
            ->with($this->isInstanceOf('BlackBoxCode\Pando\Bundle\BaseBundle\Tests\DataFixures\ORM\OneType'))
        ;

        $this->mObjectManager
            ->expects($this->once())
            ->method('flush')
        ;

        $this->mLoadTypeData->load($this->mObjectManager);
    }
}
