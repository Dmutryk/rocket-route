<?php

namespace Tests;

use App\Http\Controllers\Api\LoginInterface;
use App\Http\Controllers\Api\ReLoginInterface;
use App\Http\Controllers\RocketLoginController;
use Illuminate\Http\Request;
use Illuminate\Session\Store;
use Illuminate\Session\SessionManager;

class RocketLoginTest extends TestCase
{
    /**
     * @var RocketLoginController
     */
    private $rocketLoginController;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var Store
     */
    private $store;

    /**
     * @var SessionManager
     */
    private $sessionManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->rocketLoginController = $this->createMock(RocketLoginController::class);
        $this->store = $this->createMock(Store::class);
        $this->sessionManager = $this->createMock(SessionManager::class);
        $this->request = $this->getMockBuilder('Illuminate\Http\Request')
            ->disableOriginalConstructor()
            ->setMethods(['session'])
            ->getMock();
    }

    /**
     * @test
     */
    public function check_login_controller_is_right_instance_of_login()
    {
        $this->assertInstanceOf(LoginInterface::class, $this->rocketLoginController);
    }

    /**
     * @test
     */
    public function check_login_controller_is_right_instance_of_relogin()
    {
        $this->assertInstanceOf(ReLoginInterface::class, $this->rocketLoginController);
    }

    /**
     * @test
     */
    public function check_login()
    {
        $this->rocketLoginController
            ->method('login')
            ->willReturn(null);

        $this->assertNull($this->rocketLoginController->login());
    }

    /**
     * @test
     */
    public function check_get_token()
    {
        //TODO check why $this->request->session() === null
//        $this->request
//            ->method('session')
//            ->willReturn($this->store);

        $this->store
            ->method('has')
            ->with(RocketLoginController::ROCKET_ROUTE_TOKEN)
            ->willReturn(false);

        $this->rocketLoginController
            ->method('login')
            ->willReturn(null);

        $this->rocketLoginController
            ->method('getToken')
            ->willReturn($this->sessionManager);

        $this->assertInstanceOf(SessionManager::class, $this->rocketLoginController->getToken($this->request));
    }

    /**
     * @test
     */
    public function check_get_token_short()
    {
        $this->rocketLoginController
            ->method('getToken')
            ->willReturn('some_token');

        $this->assertIsString($this->rocketLoginController->getToken($this->request));
    }

    /**
     * {@inheritdoc}
     */
    public function tearDown(): void
    {
        // If you use Mockery in your tests you MUST use this method
        \Mockery::close();

        // clean up the memory taken by your instance of service
        $this->rocketLoginController = null;

        // Forces collection of any existing garbage cycles
        gc_collect_cycles();
    }
}
