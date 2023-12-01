<?php

namespace App\Tests\Func;

use App\DataFixtures\EventFixtures;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class EventControllerTest extends WebTestCase
{
    protected AbstractDatabaseTool $databaseTool;
    private static KernelBrowser $client;

    protected function setUp(): void
    {
        self::$client = self::createClient();

        $entityManager = self::getContainer()->get('doctrine.orm.entity_manager');
        if (!$entityManager instanceof EntityManagerInterface) {
            throw new \LogicException(sprintf('Expected instance of "%s", instance of "%s" given', EntityManagerInterface::class, get_class($entityManager)));
        }

        $metaData = $entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool = new SchemaTool($entityManager);
        $schemaTool->updateSchema($metaData);

        $databaseToolCollection = self::getContainer()->get(DatabaseToolCollection::class);
        if (!$databaseToolCollection instanceof DatabaseToolCollection) {
            throw new \LogicException(sprintf('Expected instance of "%s", instance of "%s" given', DatabaseToolCollection::class, get_class($entityManager)));
        }

        $this->databaseTool = $databaseToolCollection->get();

        $this->databaseTool->loadFixtures(
            [EventFixtures::class]
        );
    }

    public function testUpdateShouldReturnEmptyResponse(): void
    {
        $client = self::$client;

        $value = json_encode(['comment' => 'It‘s a test comment !!!!!!!!!!!!!!!!!!!!!!!!!!!']);
        $this->assertNotFalse($value);

        $client->request(
            'PUT',
            sprintf('/api/event/%d/update', EventFixtures::EVENT_1_ID),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            $value
        );

        $this->assertResponseStatusCodeSame(204);
    }

    public function testUpdateShouldReturnHttpNotFoundResponse(): void
    {
        $client = self::$client;

        $value = json_encode(['comment' => 'It‘s a test comment !!!!!!!!!!!!!!!!!!!!!!!!!!!']);
        $this->assertNotFalse($value);

        $client->request(
            'PUT',
            sprintf('/api/event/%d/update', 7897897897),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            $value
        );

        $this->assertResponseStatusCodeSame(404);

        $expectedJson = <<<JSON
              {
                "message":"Event identified by 7897897897 not found !"
              }
            JSON;

        $content = $client->getResponse()->getContent();
        $this->assertNotFalse($content);
        self::assertJsonStringEqualsJsonString($expectedJson, $content);
    }

    /**
     * @dataProvider providePayloadViolations
     */
    public function testUpdateShouldReturnBadRequest(string $payload, string $expectedResponse): void
    {
        $client = self::$client;

        $client->request(
            'PUT',
            sprintf('/api/event/%d/update', EventFixtures::EVENT_1_ID),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            $payload
        );

        self::assertResponseStatusCodeSame(400);
        $content = $client->getResponse()->getContent();
        $this->assertNotFalse($content);
        self::assertJsonStringEqualsJsonString($expectedResponse, $content);
    }

    /**
     * @return iterable<string, string[]>
     */
    public function providePayloadViolations(): iterable
    {
        yield 'comment too short' => [
            <<<JSON
              {
                "comment": "short"
                
            }
            JSON,
            <<<JSON
                {
                    "message": "This value is too short. It should have 20 characters or more."
                }
            JSON
        ];
    }
}
