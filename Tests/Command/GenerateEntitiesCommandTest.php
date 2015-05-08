<?php
namespace BlackBoxCode\Pando\Bundle\BaseBundle\Tests\Command;

use BlackBoxCode\Pando\Bundle\BaseBundle\Command\GenerateEntitiesCommand;
use BlackBoxCode\Pando\Bundle\BaseBundle\Tools\EntityMetadata;
use Symfony\Component\Console\Tester\CommandTester;

class GenerateEntitiesCommandTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject|GenerateEntitiesCommand */
    private $mGenerateEntitiesCommand;

    /** @var CommandTester */
    private $commandTester;

    public function setUp()
    {
        $this->mGenerateEntitiesCommand = $this
            ->getMockBuilder('BlackBoxCode\Pando\Bundle\BaseBundle\Command\GenerateEntitiesCommand')
            ->setMethods(['generateClassMap', 'getEntityGenerator'])
            ->getMock()
        ;

        $this->commandTester = new CommandTester($this->mGenerateEntitiesCommand);
    }

    /**
     * @test
     */
    public function execute_correctCalls()
    {
        $outputDir = 'src';

        $userMeta = new EntityMetadata();
        $userMeta
            ->setClassName('User')
            ->addInterface('BlackBoxCode\Pando\Bundle\UserBundle\Model\UserInterface')
            ->addTrait('AppBundle\Model\UserTrait')
            ->addTrait('BlackBoxCode\Pando\Bundle\UserBundle\Model\UserTrait')
        ;

        $idMeta = new EntityMetadata();
        $idMeta
            ->setClassName('Id')
            ->addInterface('BlackBoxCode\Pando\Bundle\BaseBundle\Model\IdInterface')
            ->addTrait('BlackBoxCode\Pando\Bundle\BaseBundle\Model\IdTrait')
        ;

        $classMap = array_merge(
            $userMeta->getInterfaces(),
            $userMeta->getTraits(),
            $idMeta->getInterfaces(),
            $idMeta->getTraits()
        );

        $mEntityGenerator = $this
            ->getMockBuilder('BlackBoxCode\Pando\Bundle\BaseBundle\Tools\EntityGenerator')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->mGenerateEntitiesCommand
            ->expects($this->once())
            ->method('getEntityGenerator')
            ->willReturn($mEntityGenerator)
        ;

        $this->mGenerateEntitiesCommand
            ->expects($this->once())
            ->method('generateClassMap')
            ->willReturn($classMap)
        ;

        $mEntityGenerator
            ->expects($this->exactly(2))
            ->method('createEntityMetadata')
            ->will(
                $this->returnValueMap([
                    ['User', ['Interface' => $userMeta->getInterfaces(), 'Trait' => $userMeta->getTraits()], $userMeta],
                    ['Id', ['Interface' => $idMeta->getInterfaces(), 'Trait' => $idMeta->getTraits()], $idMeta]
                ])
            )
        ;

        $mEntityGenerator
            ->expects($this->exactly(2))
            ->method('writeEntityToFile')
            ->with($this->isInstanceOf(get_class(new EntityMetadata())))
            ->willReturnOnConsecutiveCalls(
                $this->throwException(new \InvalidArgumentException()),
                null
            )
        ;

        $this->commandTester->execute([]);
    }
}
