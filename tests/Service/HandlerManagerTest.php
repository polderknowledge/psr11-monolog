<?php
declare(strict_types=1);

namespace WShafer\PSR11MonoLog\Test\Service;

use Monolog\Formatter\LineFormatter;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use WShafer\PSR11MonoLog\Config\HandlerConfig;
use WShafer\PSR11MonoLog\Config\MainConfig;
use WShafer\PSR11MonoLog\MapperInterface;
use WShafer\PSR11MonoLog\Service\FormatterManager;
use WShafer\PSR11MonoLog\Service\HandlerManager;
use WShafer\PSR11MonoLog\Test\Stub\HandlerStub;

/**
 * @covers \WShafer\PSR11MonoLog\Service\HandlerManager
 */
class HandlerManagerTest extends TestCase
{
    /** @var HandlerManager */
    protected $service;

    /** @var \PHPUnit_Framework_MockObject_MockObject|ContainerInterface */
    protected $mockContainer;

    /** @var \PHPUnit_Framework_MockObject_MockObject|MainConfig */
    protected $mockConfig;

    /** @var \PHPUnit_Framework_MockObject_MockObject|MapperInterface */
    protected $mockMapper;

    /** @var \PHPUnit_Framework_MockObject_MockObject|HandlerConfig */
    protected $mockServiceConfig;

    /** @var \PHPUnit_Framework_MockObject_MockObject|FormatterManager */
    protected $mockFormatterManager;

    public function setup()
    {
        $this->mockContainer = $this->createMock(ContainerInterface::class);

        $this->mockConfig = $this->getMockBuilder(MainConfig::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->mockServiceConfig = $this->getMockBuilder(HandlerConfig::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->mockMapper = $this->createMock(MapperInterface::class);

        $this->mockFormatterManager = $this->getMockBuilder(FormatterManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->service = new HandlerManager(
            $this->mockConfig,
            $this->mockMapper,
            $this->mockContainer
        );

        $this->assertInstanceOf(HandlerManager::class, $this->service);
    }

    public function testConstructor()
    {
    }

    public function testGetAndSetFormatterManager()
    {
        $this->service->setFormatterManager($this->mockFormatterManager);
        $result =  $this->service->getFormatterManager();
        $this->assertEquals($this->mockFormatterManager, $result);
    }

    public function testGetServiceConfig()
    {
        $this->mockConfig->expects($this->exactly(1))
            ->method('getHandlerConfig')
            ->with('my-config-name')
            ->willReturn($this->mockServiceConfig);

        $result = $this->service->getServiceConfig('my-config-name');
        $this->assertEquals($this->mockServiceConfig, $result);
    }

    public function testGetFromParentOnly()
    {
        $expected = $this->getMockBuilder(HandlerStub::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->service->setFormatterManager($this->mockFormatterManager);

        $this->mockContainer->expects($this->exactly(2))
            ->method('has')
            ->with('my-service')
            ->willReturn(true);

        $this->mockContainer->expects($this->exactly(1))
            ->method('get')
            ->with('my-service')
            ->willReturn($expected);

        $this->mockConfig->expects($this->never())
            ->method('hasHandlerConfig');

        $this->mockServiceConfig->expects($this->never())
            ->method('getFormatter')
            ->willReturn(null);

        $result = $this->service->get('my-service');
        $this->assertEquals($expected, $result);
    }

    public function testGetWithAdditionalFormatter()
    {
        $expected = $this->getMockBuilder(HandlerStub::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->service->setFormatterManager($this->mockFormatterManager);

        $this->mockContainer->expects($this->exactly(2))
            ->method('has')
            ->with('my-service')
            ->willReturn(true);

        $this->mockContainer->expects($this->exactly(1))
            ->method('get')
            ->with('my-service')
            ->willReturn($expected);

        $this->mockConfig->expects($this->once())
            ->method('getHandlerConfig')
            ->with('my-service')
            ->willReturn($this->mockServiceConfig);

        $mockFormatter = $this->getMockBuilder(LineFormatter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->mockServiceConfig->expects($this->once())
            ->method('getFormatter')
            ->willReturn('my-formatter');

        $expected->expects($this->once())
            ->method('setFormatter')
            ->with($mockFormatter)
            ->willReturn(true);

        $this->mockFormatterManager->expects($this->once())
            ->method('has')
            ->with('my-formatter')
            ->willReturn(true);

        $this->mockFormatterManager->expects($this->once())
            ->method('get')
            ->with('my-formatter')
            ->willReturn($mockFormatter);

        $result = $this->service->get('my-service');
        $this->assertEquals($expected, $result);
    }

    public function testGetFromPreviouslyConstructedService()
    {
        $expected = $this->getMockBuilder(HandlerStub::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->service->setFormatterManager($this->mockFormatterManager);

        $this->mockContainer->expects($this->exactly(2))
            ->method('has')
            ->with('my-service')
            ->willReturn(true);

        $this->mockContainer->expects($this->exactly(1))
            ->method('get')
            ->with('my-service')
            ->willReturn($expected);

        $this->mockConfig->expects($this->never())
            ->method('hasHandlerConfig');

        $this->mockServiceConfig->expects($this->never())
            ->method('getFormatter')
            ->willReturn(null);

        $result = $this->service->get('my-service');
        $this->assertEquals($expected, $result);

        // No additional dependency calls should happen here
        $result = $this->service->get('my-service');
        $this->assertEquals($expected, $result);
    }

    /** @expectedException \WShafer\PSR11MonoLog\Exception\MissingServiceException */
    public function testGetMissingFormatterManager()
    {
        $expected = $this->getMockBuilder(HandlerStub::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->mockContainer->expects($this->exactly(2))
            ->method('has')
            ->with('my-service')
            ->willReturn(true);

        $this->mockContainer->expects($this->exactly(1))
            ->method('get')
            ->with('my-service')
            ->willReturn($expected);

        $this->mockConfig->expects($this->once())
            ->method('getHandlerConfig')
            ->with('my-service')
            ->willReturn($this->mockServiceConfig);

        $this->mockServiceConfig->expects($this->once())
            ->method('getFormatter')
            ->willReturn('my-formatter');

        $expected->expects($this->never())
            ->method('setFormatter');

        $this->mockFormatterManager->expects($this->never())
            ->method('has');

        $this->mockFormatterManager->expects($this->never())
            ->method('get');

        $result = $this->service->get('my-service');
        $this->assertEquals($expected, $result);
    }

    /** @expectedException \WShafer\PSR11MonoLog\Exception\UnknownServiceException */
    public function testGetMissingFormatterService()
    {
        $expected = $this->getMockBuilder(HandlerStub::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->service->setFormatterManager($this->mockFormatterManager);

        $this->mockContainer->expects($this->exactly(2))
            ->method('has')
            ->with('my-service')
            ->willReturn(true);

        $this->mockContainer->expects($this->exactly(1))
            ->method('get')
            ->with('my-service')
            ->willReturn($expected);

        $this->mockConfig->expects($this->once())
            ->method('getHandlerConfig')
            ->with('my-service')
            ->willReturn($this->mockServiceConfig);

        $this->mockServiceConfig->expects($this->once())
            ->method('getFormatter')
            ->willReturn('my-formatter');

        $expected->expects($this->never())
            ->method('setFormatter');

        $this->mockFormatterManager->expects($this->once())
            ->method('has')
            ->with('my-formatter')
            ->willReturn(false);

        $this->mockFormatterManager->expects($this->never())
            ->method('get');

        $this->service->get('my-service');
    }

    public function testHasServiceConfig()
    {
        $this->mockConfig->expects($this->once())
            ->method('hasHandlerConfig')
            ->with('my-config-name')
            ->willReturn(true);

        $result = $this->service->hasServiceConfig('my-config-name');
        $this->assertTrue($result);
    }
}
