<?php

namespace GraphAware\Neo4j\OGM\Tests\Integration;

use GraphAware\Neo4j\OGM\Tests\Integration\Model\User;

class SingleEntityTest extends IntegrationTestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->clearDb();
    }

    public function testEntityIsPersisted()
    {
        $user = new User('neo', 33);
        $this->em->persist($user);
        $this->em->flush();

        $query = 'MATCH (n:User {login:"neo"}) RETURN n';
        $result = $this->client->run($query);

        $this->assertCount(1, $result->records());
        $record = $result->records()[0];
        $userNode = $record->value("n");
        $this->assertEquals("neo", $userNode->value("login"));
        $this->assertEquals(33, $userNode->value("age"));
        $this->assertCount(2, $userNode->values());
        $this->assertCount(1, $userNode->labels());
    }

    public function testEntityCanBeRetrieved()
    {
        $user = new User('neo', 33);
        $this->em->persist($user);
        $this->em->flush();

        $this->resetEm();

        $repository = $this->em->getRepository(User::class);
        $user = $repository->findOneBy('login', 'neo');

        $this->assertEquals('neo', $user->getLogin());
        $this->assertEquals(33, $user->getAge());
    }

    public function testNullPropertiesAreNotPersisted()
    {
        $user = new User('neo');
        $this->em->persist($user);
        $this->em->flush();

        $query = 'MATCH (n:User {login: "neo"}) RETURN n';
        $result = $this->client->run($query);
        $record = $result->records()[0];
        $userNode = $record->value('n');
        $this->assertFalse($userNode->hasValue('age'));
    }
}