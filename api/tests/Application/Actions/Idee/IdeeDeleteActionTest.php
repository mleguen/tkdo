<?php

declare(strict_types=1);

namespace Tests\Application\Actions\Idee;

use App\Infrastructure\Persistence\Idee\InMemoryIdeeReference;
use Exception;
use Tests\Application\Actions\ActionTestCase;

class IdeeDeleteActionTest extends ActionTestCase
{
    /**
     * @var Idee
     */
    private $idee;

    public function setUp()
    {
        parent::setup();
        $this->idee = new InMemoryIdeeReference(0);
        $this->ideeRepositoryProphecy
            ->read($this->idee->getId(), true)
            ->willReturn($this->idee);
    }

    public function testAction()
    {
        $this->ideeRepositoryProphecy
            ->delete($this->idee)
            ->willReturn()
            ->shouldBeCalledOnce();

        $response = $this->handleAuthorizedRequest(
            'DELETE',
            "/idee/{$this->idee->getId()}"
        );

        $this->assertEqualsResponse(
            200,
            <<<'EOT'
null
EOT
            , $response
        );
    }

    public function testActionEchecDelete()
    {
        $this->ideeRepositoryProphecy
            ->delete($this->idee)
            ->willThrow(new Exception('échec de delete'))
            ->shouldBeCalledOnce();

        $response = $this->handleAuthorizedRequest(
            'DELETE',
            "/idee/{$this->idee->getId()}"
        );

        $this->assertEqualsResponse(
            500,
            <<<'EOT'
{
    "type": "SERVER_ERROR",
    "description": "\u00e9chec de delete"
}
EOT
            , $response
        );
    }

    public function testActionNonAutorise()
    {
        $response = $this->handleRequest(
            'DELETE',
            "/idee/{$this->idee->getId()}"
        );

        $this->assertEqualsResponse(
            401,
            <<<'EOT'
{
    "type": "UNAUTHENTICATED",
    "description": "Unauthorized."
}
EOT
            , $response
        );
    }
}
